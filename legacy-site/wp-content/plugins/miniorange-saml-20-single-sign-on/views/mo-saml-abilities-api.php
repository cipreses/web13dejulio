<?php
/**
 * File to display Abilities API settings.
 *
 * @package miniorange-saml-20-single-sign-on\views
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Used to show the UI part of the Abilities API feature to user screen.
 *
 * @return void
 */
function mo_saml_display_abilities_api_page() {
	$abilities_api_enabled = get_option( Mo_Saml_Options_Enum_Sso_Login::MO_SAML_ENABLE_ABILITIES_API );
	global $wp_version;
	mo_saml_display_plugin_header();
	?>  
	<div class="bg-main-cstm mo-saml-margin-left mo-saml-bootstrap-pb-5">
		<div class="mo-saml-bootstrap-row mo-saml-bootstrap-container-fluid">
			<div class="mo-saml-bootstrap-col-md-8 mo-saml-bootstrap-mt-4">
				<div class="mo-saml-bootstrap-p-4 shadow-cstm mo-saml-bootstrap-bg-white mo-saml-bootstrap-rounded">
					<form action="" method="post" id="mo_saml_abilities_api">
						<?php wp_nonce_field( 'mo_saml_abilities_api' ); ?>
						<input type="hidden" name="option" value="mo_saml_abilities_api" />
						<div class="mo-saml-bootstrap-row">
							<div class="mo-saml-bootstrap-col-md-6">
								<h4>
								<span class="entity-info"><?php esc_html_e( 'Abilities API Settings', 'miniorange-saml-20-single-sign-on' ); ?>
									<a href="https://www.miniorange.com/blog/wordpress-api-abilities-and-mcp-ai-agents/" class="mo-saml-bootstrap-text-dark" target="_blank">
										<svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
											<path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z" />
											<path d="M5.255 5.786a.237.237 0 0 0 .241.247h.825c.138 0 .248-.113.266-.25.09-.656.54-1.134 1.342-1.134.686 0 1.314.343 1.314 1.168 0 .635-.374.927-.965 1.371-.673.489-1.206 1.06-1.168 1.987l.003.217a.25.25 0 0 0 .25.246h.811a.25.25 0 0 0 .25-.25v-.105c0-.718.273-.927 1.01-1.486.609-.463 1.244-.977 1.244-2.056 0-1.511-1.276-2.241-2.673-2.241-1.267 0-2.655.59-2.75 2.286zm1.557 5.763c0 .533.425.927 1.01.927.609 0 1.028-.394 1.028-.927 0-.552-.42-.94-1.029-.94-.584 0-1.009.388-1.009.94z" />
										</svg>
									</a>
								</span>
								</h4>
							</div>
							<div class="mo-saml-bootstrap-col-md-6 mo-saml-bootstrap-text-end">
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=mo_saml_settings' ) ); ?>" class="mo-saml-bootstrap-btn btn-cstm mo-saml-bootstrap-ms-3"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-left" viewBox="0 0 16 16">
										<path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8z" />
									</svg>&nbsp;<?php esc_html_e( 'Back To Plugin Configuration', 'miniorange-saml-20-single-sign-on' ); ?></a>
							</div>
						</div>
						<div class="form-head"></div>
						
						<div class="call-setup-div mo-saml-bootstrap-mt-4">
							
							<?php if ( version_compare( $wp_version, '6.9', '>=' ) ) : ?>
								<h6 class="call-setup-heading">
									<strong><?php esc_html_e( 'Pre-requisites to enable the Abilities API for WP 6.9 or higher', 'miniorange-saml-20-single-sign-on' ); ?></strong>
								</h6>
								<ul class="mo-saml-bootstrap-mt-2 mo-saml-bootstrap-mb-0 mo-saml-bootstrap-text-secondary" style="list-style-type: disc; padding-left: 20px;">
									<li>
										<?php esc_html_e( 'Install', 'miniorange-saml-20-single-sign-on' ); ?>
										<a href="https://github.com/WordPress/mcp-adapter/releases" target="_blank" rel="noopener noreferrer" class="mo-saml-bootstrap-text-blue">
											<?php esc_html_e( 'MCP Adapter', 'miniorange-saml-20-single-sign-on' ); ?>
										</a>
										<?php esc_html_e( 'plugin and activate it.', 'miniorange-saml-20-single-sign-on' ); ?>
									</li>
								</ul>
							<?php elseif ( version_compare( $wp_version, '6.8', '>=' ) ) : ?>
								<h6 class="call-setup-heading">
									<strong><?php esc_html_e( 'Pre-requisites to enable the Abilities API for WP 6.8 or higher', 'miniorange-saml-20-single-sign-on' ); ?></strong>
								</h6>
								<ul class="mo-saml-bootstrap-mt-2 mo-saml-bootstrap-mb-0 mo-saml-bootstrap-text-secondary" style="list-style-type: disc; padding-left: 20px;">
									<li>
										<?php esc_html_e( 'Install', 'miniorange-saml-20-single-sign-on' ); ?>
										<a href="https://github.com/WordPress/mcp-adapter/releases" target="_blank" rel="noopener noreferrer" class="mo-saml-bootstrap-text-blue">
											<?php esc_html_e( 'MCP Adapter', 'miniorange-saml-20-single-sign-on' ); ?>
										</a>
										<?php esc_html_e( 'plugin and activate it.', 'miniorange-saml-20-single-sign-on' ); ?>
									</li>
									<li>
										<?php esc_html_e( 'Install', 'miniorange-saml-20-single-sign-on' ); ?>
										<a href="https://github.com/WordPress/abilities-api/releases/" target="_blank" rel="noopener noreferrer" class="mo-saml-bootstrap-text-blue">
											<?php esc_html_e( 'Abilities API', 'miniorange-saml-20-single-sign-on' ); ?>
										</a>
										<?php esc_html_e( 'plugin and activate it.', 'miniorange-saml-20-single-sign-on' ); ?>
									</li>
								</ul>
							<?php else : ?>
								<h6 class="call-setup-heading">
									<strong><?php esc_html_e( 'Pre-requisites to enable the Abilities API for WP 6.8 or higher', 'miniorange-saml-20-single-sign-on' ); ?></strong>
								</h6>
								<ul class="mo-saml-bootstrap-mt-2 mo-saml-bootstrap-mb-0 mo-saml-bootstrap-text-secondary" style="list-style-type: disc; padding-left: 20px;">
									<li><?php esc_html_e( 'Upgrade WordPress to version 6.8 or higher.', 'miniorange-saml-20-single-sign-on' ); ?></li>
									<li>
										<?php esc_html_e( 'Install', 'miniorange-saml-20-single-sign-on' ); ?>
										<a href="https://github.com/WordPress/abilities-api/releases/" target="_blank" rel="noopener noreferrer" class="mo-saml-bootstrap-text-blue">
											<?php esc_html_e( 'Abilities API', 'miniorange-saml-20-single-sign-on' ); ?>
										</a>
										<?php esc_html_e( 'plugin and activate it.', 'miniorange-saml-20-single-sign-on' ); ?>
									</li>
									<li>
										<?php esc_html_e( 'Install', 'miniorange-saml-20-single-sign-on' ); ?>
										<a href="https://github.com/WordPress/mcp-adapter/releases" target="_blank" rel="noopener noreferrer" class="mo-saml-bootstrap-text-blue">
											<?php esc_html_e( 'MCP Adapter', 'miniorange-saml-20-single-sign-on' ); ?>
										</a>
										<?php esc_html_e( 'plugin and activate it.', 'miniorange-saml-20-single-sign-on' ); ?>
									</li>
								</ul>
							<?php endif; ?>
						</div>

						<div class="mo-saml-bootstrap-row mo-saml-bootstrap-align-items-top mo-saml-bootstrap-mt-4">
							<div class="mo-saml-bootstrap-col-md-9">
								<h6 class="text-secondary">
									<b><?php esc_html_e( 'Enable Abilities API of WP SAML SSO Plugin', 'miniorange-saml-20-single-sign-on' ); ?></b>
								</h6>
							</div>
							<div class="mo-saml-bootstrap-col-md-3 mo-saml-bootstrap-ps-0">
								<input type="checkbox" id="mo_saml_enable_abilities_api" name="mo_saml_enable_abilities_api" class="mo-saml-switch" value="true" onchange="submit();" 
								<?php
								if ( 'true' === $abilities_api_enabled ) {
									echo ' checked ';
								}
								?>
								/>
								<label class="mo-saml-switch-label" for="mo_saml_enable_abilities_api"></label>
							</div>
						</div>

						<style>
							.mo-saml-guide-link:hover { border-color: #2271b1 !important; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
						</style>
						<div class="call-setup-div mo-saml-bootstrap-mt-4">
							<h6 class="call-setup-heading">
								<strong><span class="mo-saml-bootstrap-text-danger"><?php esc_html_e( 'Note: ', 'miniorange-saml-20-single-sign-on' ); ?></span><?php esc_html_e( 'Enabling this option will make SSO abilities publicly accessible when connected with MCP.', 'miniorange-saml-20-single-sign-on' ); ?></strong>
							</h6>
							<div class="mo-saml-bootstrap-row mo-saml-bootstrap-g-3 mo-saml-bootstrap-mt-2">
								<div class="mo-saml-bootstrap-col-md-4">
									<a href="https://developer.wordpress.org/news/2026/02/from-abilities-to-ai-agents-introducing-the-wordpress-mcp-adapter/#claude-desktop" target="_blank" rel="noopener noreferrer" class="mo-saml-guide-link mo-saml-bootstrap-d-block mo-saml-bootstrap-p-3 mo-saml-bootstrap-border mo-saml-bootstrap-rounded mo-saml-bootstrap-text-decoration-none mo-saml-bootstrap-text-dark" style="border-color: #ddd !important; transition: border-color 0.2s, box-shadow 0.2s;">
										<span class="mo-saml-bootstrap-d-flex mo-saml-bootstrap-align-items-center mo-saml-bootstrap-mb-2">
											<img src="<?php echo esc_url( Mo_SAML_Utilities::mo_saml_get_plugin_dir_url() . 'images/claude-ai-icon.svg' ); ?>" alt="<?php esc_attr_e( 'Claude', 'miniorange-saml-20-single-sign-on' ); ?>" width="20" height="20" class="mo-saml-bootstrap-me-2" />
											<strong><?php esc_html_e( 'Claude Desktop', 'miniorange-saml-20-single-sign-on' ); ?></strong>
										</span>
										<span class="mo-saml-bootstrap-text-secondary mo-saml-bootstrap-small"><?php esc_html_e( 'Connect Claude Desktop to your WordPress site via MCP.', 'miniorange-saml-20-single-sign-on' ); ?></span>
										<span class="mo-saml-bootstrap-d-block mo-saml-bootstrap-mt-2 mo-saml-bootstrap-text-primary mo-saml-bootstrap-small"><?php esc_html_e( 'View setup guide →', 'miniorange-saml-20-single-sign-on' ); ?></span>
									</a>
								</div>
								<div class="mo-saml-bootstrap-col-md-4">
									<a href="https://developer.wordpress.org/news/2026/02/from-abilities-to-ai-agents-introducing-the-wordpress-mcp-adapter/#cursor" target="_blank" rel="noopener noreferrer" class="mo-saml-guide-link mo-saml-bootstrap-d-block mo-saml-bootstrap-p-3 mo-saml-bootstrap-border mo-saml-bootstrap-rounded mo-saml-bootstrap-text-decoration-none mo-saml-bootstrap-text-dark" style="border-color: #ddd !important; transition: border-color 0.2s, box-shadow 0.2s;">
										<span class="mo-saml-bootstrap-d-flex mo-saml-bootstrap-align-items-center mo-saml-bootstrap-mb-2">
											<img src="<?php echo esc_url( Mo_SAML_Utilities::mo_saml_get_plugin_dir_url() . 'images/cursor-ai-code-icon.svg' ); ?>" alt="<?php esc_attr_e( 'Cursor', 'miniorange-saml-20-single-sign-on' ); ?>" width="20" height="20" class="mo-saml-bootstrap-me-2" />
											<strong><?php esc_html_e( 'Cursor', 'miniorange-saml-20-single-sign-on' ); ?></strong>
										</span>
										<span class="mo-saml-bootstrap-text-secondary mo-saml-bootstrap-small"><?php esc_html_e( 'Connect Cursor IDE to your WordPress site via MCP.', 'miniorange-saml-20-single-sign-on' ); ?></span>
										<span class="mo-saml-bootstrap-d-block mo-saml-bootstrap-mt-2 mo-saml-bootstrap-text-primary mo-saml-bootstrap-small"><?php esc_html_e( 'View setup guide →', 'miniorange-saml-20-single-sign-on' ); ?></span>
									</a>
								</div>
								<div class="mo-saml-bootstrap-col-md-4">
									<a href="https://plugins.miniorange.com/wordpress-chatgpt-integration" target="_blank" rel="noopener noreferrer" class="mo-saml-guide-link mo-saml-bootstrap-d-block mo-saml-bootstrap-p-3 mo-saml-bootstrap-border mo-saml-bootstrap-rounded mo-saml-bootstrap-text-decoration-none mo-saml-bootstrap-text-dark" style="border-color: #ddd !important; transition: border-color 0.2s, box-shadow 0.2s;">
										<span class="mo-saml-bootstrap-d-flex mo-saml-bootstrap-align-items-center mo-saml-bootstrap-mb-2">
											<img src="<?php echo esc_url( Mo_SAML_Utilities::mo_saml_get_plugin_dir_url() . 'images/chatgpt-icon.svg' ); ?>" alt="<?php esc_attr_e( 'ChatGPT', 'miniorange-saml-20-single-sign-on' ); ?>" width="20" height="20" class="mo-saml-bootstrap-me-2" />
											<strong><?php esc_html_e( 'ChatGPT', 'miniorange-saml-20-single-sign-on' ); ?></strong>
										</span>
										<span class="mo-saml-bootstrap-text-secondary mo-saml-bootstrap-small"><?php esc_html_e( 'Connect ChatGPT to your WordPress site using guide.', 'miniorange-saml-20-single-sign-on' ); ?></span>
										<span class="mo-saml-bootstrap-d-block mo-saml-bootstrap-mt-2 mo-saml-bootstrap-text-primary mo-saml-bootstrap-small"><?php esc_html_e( 'View setup guide →', 'miniorange-saml-20-single-sign-on' ); ?></span>
									</a>
								</div>
							</div>
						</div>
					</form>
				</div>
			</div>
			<?php mo_saml_display_support_form(); ?>
		</div>
	</div>
	<?php
}
