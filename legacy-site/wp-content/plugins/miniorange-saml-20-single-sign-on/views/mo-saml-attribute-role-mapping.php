<?php
/**
 * File to display sections of Attribute and Role Mapping.
 *
 * @package miniorange-saml-20-single-sign-on\views
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Function to display Attribute/Role Mapping tab.
 *
 * @return void
 */
function mo_saml_save_optional_config() {
	$default_role = get_option( 'saml_am_default_user_role' );
	if ( empty( $default_role ) ) {
		$default_role = get_option( 'default_role' );
	}
	$wp_roles = new WP_Roles();
	$roles    = $wp_roles->get_names();

	//phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Ignore the nonce verification.
	$active_subtab = ! empty( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'attribute';
	if ( ! in_array( $active_subtab, array( 'attribute', 'role' ), true ) ) {
		$active_subtab = 'attribute';
	}
	?>
	<div class="mo-saml-bootstrap-row mo-saml-bootstrap-container-fluid" action="" id="attr-role-tab-form">
		<div class="mo-saml-bootstrap-col-md-8 mo-saml-bootstrap-mt-4 mo-saml-bootstrap-ms-5">
			<?php mo_saml_display_subtabs( $active_subtab ); ?>
			<div class="mo-saml-subtab-content mo-saml-bootstrap-p-4 shadow-cstm mo-saml-bootstrap-bg-white">
				<?php if ( 'attribute' === $active_subtab ) : ?>
					<div class="mo-saml-subtab-pane" id="mo-saml-attribute-subtab">
						<?php mo_saml_display_attribute_mapping(); ?>
					</div>
				<?php else : ?>
					<div class="mo-saml-subtab-pane" id="mo-saml-role-subtab">
						<?php mo_saml_display_role_mapping( $default_role, $roles ); ?>
					</div>
				<?php endif; ?>
			</div>
		</div>
		<?php mo_saml_display_support_form( true ); ?>
	</div>
	<?php
}

/**
 * Function to display subtabs for Attribute/Role Mapping.
 *
 * @param string $active_subtab The currently active subtab.
 * @return void
 */
function mo_saml_display_subtabs( $active_subtab ) {
	if ( isset( $_SERVER['REQUEST_URI'] ) ) {
		$current_url = esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) );
	} else {
		$current_url = '';
	}
	$attribute_url = add_query_arg( array( 'tab' => 'attribute' ), $current_url );
	$role_url      = add_query_arg( array( 'tab' => 'role' ), $current_url );
	?>
	<div class="mo-saml-subtabs">
		<div class="mo-saml-subtabs-nav mo-saml-bootstrap-d-flex">
			<a href="<?php echo esc_url( $attribute_url ); ?>" class="mo-saml-subtab-nav <?php echo esc_attr( 'attribute' === $active_subtab ? 'mo-saml-subtab-nav-active' : '' ); ?>">
				<?php esc_html_e( 'Attribute Mapping', 'miniorange-saml-20-single-sign-on' ); ?> <svg style="margin:-4px auto;" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 520 480" fill="none">
<path d="M384.994 138.665L392.555 153.785C403.316 175.336 392.945 201.949 365.082 209.696C364.73 209.813 326.138 220.172 314.417 185.066L303.12 151.133L335.267 118.972L255.941 0L176.616 118.972L208.763 151.133L197.466 185.066C184.538 223.8 131.333 214.681 118.786 187.367C113.834 176.612 114.039 164.379 119.328 153.785L126.874 138.679L0 93.3903L60.8969 327.116L90.9038 357.123H255.941H420.979L450.986 327.116L512 93.4053L384.994 138.665Z" fill="#FED843"/>
<path d="M450.986 327.116L512 93.4053L384.994 138.665L392.555 153.785C403.316 175.336 392.945 201.949 365.082 209.696C364.73 209.813 326.137 220.172 314.417 185.066L303.12 151.133L335.267 118.972L255.941 0V357.123H420.979L450.986 327.116Z" fill="#FABE2C"/>
<path d="M255.942 327.116H60.897V402.133H255.942H450.986V327.116H255.942Z" fill="#FABE2C"/>
<path d="M255.941 327.116H450.986V402.133H255.941V327.116Z" fill="#FF9100"/>
<path d="M180.867 327.103L255.873 252.097L330.879 327.103L255.873 402.108L180.867 327.103Z" fill="#FABE2C"/>
<path d="M255.941 252.099V402.133L330.959 327.116L255.941 252.099Z" fill="#FF9100"/>
</svg>
			</a>
			<a href="<?php echo esc_url( $role_url ); ?>" class="mo-saml-subtab-nav <?php echo esc_attr( 'role' === $active_subtab ? 'mo-saml-subtab-nav-active' : '' ); ?>">
				<?php esc_html_e( 'Role Mapping', 'miniorange-saml-20-single-sign-on' ); ?>
			</a>
		</div>
	</div>
	<?php
}

/**
 * Function to Display Attribute Mapping.
 *
 * @return void
 */
function mo_saml_display_attribute_mapping() {
	?>
	<div class="mo-saml-bootstrap-row align-items-top">
			<div class="mo-saml-bootstrap-col-md-12">
				<h4 class="form-head">
					<span class="entity-info"><?php esc_html_e( 'Attribute Mapping', 'miniorange-saml-20-single-sign-on' ); ?>
						<a href="https://developers.miniorange.com/docs/saml/wordpress/attribute-mapping?utm_source=saml_plugin&utm_medium=attribute_mapping&utm_campaign=saml_plugin_internal" class="mo-saml-bootstrap-text-dark" target="_blank">
							<svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
								<path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z" />
								<path d="M5.255 5.786a.237.237 0 0 0 .241.247h.825c.138 0 .248-.113.266-.25.09-.656.54-1.134 1.342-1.134.686 0 1.314.343 1.314 1.168 0 .635-.374.927-.965 1.371-.673.489-1.206 1.06-1.168 1.987l.003.217a.25.25 0 0 0 .25.246h.811a.25.25 0 0 0 .25-.25v-.105c0-.718.273-.927 1.01-1.486.609-.463 1.244-.977 1.244-2.056 0-1.511-1.276-2.241-2.673-2.241-1.267 0-2.655.59-2.75 2.286zm1.557 5.763c0 .533.425.927 1.01.927.609 0 1.028-.394 1.028-.927 0-.552-.42-.94-1.029-.94-.584 0-1.009.388-1.009.94z" />
							</svg>
						</a>
					</span>
				</h4>
			</div>
		</div>
	<div class="prem-info mo-saml-bootstrap-mt-3 mo-saml-bootstrap-d-block">
			<div class="prem-icn nameid-prem-img sso-btn-prem-img">
			<svg class="crown_img" xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 520 480" fill="none">
<path d="M384.994 138.665L392.555 153.785C403.316 175.336 392.945 201.949 365.082 209.696C364.73 209.813 326.138 220.172 314.417 185.066L303.12 151.133L335.267 118.972L255.941 0L176.616 118.972L208.763 151.133L197.466 185.066C184.538 223.8 131.333 214.681 118.786 187.367C113.834 176.612 114.039 164.379 119.328 153.785L126.874 138.679L0 93.3903L60.8969 327.116L90.9038 357.123H255.941H420.979L450.986 327.116L512 93.4053L384.994 138.665Z" fill="#FED843"/>
<path d="M450.986 327.116L512 93.4053L384.994 138.665L392.555 153.785C403.316 175.336 392.945 201.949 365.082 209.696C364.73 209.813 326.137 220.172 314.417 185.066L303.12 151.133L335.267 118.972L255.941 0V357.123H420.979L450.986 327.116Z" fill="#FABE2C"/>
<path d="M255.942 327.116H60.897V402.133H255.942H450.986V327.116H255.942Z" fill="#FABE2C"/>
<path d="M255.941 327.116H450.986V402.133H255.941V327.116Z" fill="#FF9100"/>
<path d="M180.867 327.103L255.873 252.097L330.879 327.103L255.873 402.108L180.867 327.103Z" fill="#FABE2C"/>
<path d="M255.941 252.099V402.133L330.959 327.116L255.941 252.099Z" fill="#FF9100"/>
				<p class="prem-info-text"><?php esc_html_e( 'The basic attributes are configurable in Standard, Premium, Enterprise and All-Inclusive plans. Custom Attributes are configurable in Premium and higher plans.', 'miniorange-saml-20-single-sign-on' ); ?><a href="<?php echo esc_url( Mo_Saml_External_Links::LANDING_PAGE . '?utm_source=saml_plugin&utm_medium=upgrade_attribute&utm_campaign=saml_plugin_internal#pricing' ); ?>" target="_blank" class="mo-saml-bootstrap-text-warning"><?php esc_html_e( 'Click here to upgrade', 'miniorange-saml-20-single-sign-on' ); ?></a></p>
			</div>
			<div class="mo-saml-bootstrap-row align-items-top">
				<div class="mo-saml-bootstrap-col-md-3">
					<h6 class="mo-saml-bootstrap-text-secondary"><?php esc_html_e( 'Username (required) ', 'miniorange-saml-20-single-sign-on' ); ?></span>:</h6>
				</div>
				<div class="mo-saml-bootstrap-col-md-6">
					<p class="mo-saml-bootstrap-mt-0">NameID</p>
				</div>
			</div>
			<div class="mo-saml-bootstrap-row align-items-top mo-saml-bootstrap-mt-2">
				<div class="mo-saml-bootstrap-col-md-3">
					<h6 class="mo-saml-bootstrap-text-secondary"><?php esc_html_e( 'Email (required) :', 'miniorange-saml-20-single-sign-on' ); ?></h6>
				</div>
				<div class="mo-saml-bootstrap-col-md-6">
					<p class="mo-saml-bootstrap-mt-0">NameID</p>
				</div>
			</div>
			<div class="mo-saml-bootstrap-row align-items-top mo-saml-bootstrap-mt-3">
				<div class="mo-saml-bootstrap-col-md-3">
					<h6 class="mo-saml-bootstrap-text-secondary"><?php esc_html_e( 'First Name :', 'miniorange-saml-20-single-sign-on' ); ?></h6>
				</div>
				<div class="mo-saml-bootstrap-col-md-6">
					<input type="text" name="saml_am_first_name" placeholder="<?php esc_attr_e( 'Enter attribute name for First Name', 'miniorange-saml-20-single-sign-on' ); ?>" class="mo-saml-bootstrap-w-100 mo-saml-bootstrap-bg-light cursor-disabled" value="" disabled>
				</div>
			</div>
			<div class="mo-saml-bootstrap-row align-items-top mo-saml-bootstrap-mt-4">
				<div class="mo-saml-bootstrap-col-md-3">
					<h6 class="mo-saml-bootstrap-text-secondary"><?php esc_html_e( 'Last Name :', 'miniorange-saml-20-single-sign-on' ); ?></h6>
				</div>
				<div class="mo-saml-bootstrap-col-md-6">
					<input type="text" name="saml_am_last_name" placeholder="<?php esc_attr_e( 'Enter attribute name for Last Name', 'miniorange-saml-20-single-sign-on' ); ?>" class="mo-saml-bootstrap-w-100 mo-saml-bootstrap-bg-light cursor-disabled" value="" disabled>
				</div>
			</div>
			<div class="mo-saml-bootstrap-row align-items-top mo-saml-bootstrap-mt-4">
				<div class="mo-saml-bootstrap-col-md-3">
					<h6 class="mo-saml-bootstrap-text-secondary"><?php esc_html_e( 'Group/Role :', 'miniorange-saml-20-single-sign-on' ); ?></h6>
				</div>
				<div class="mo-saml-bootstrap-col-md-6">
					<input type="text" name="" placeholder="<?php esc_attr_e( 'Enter attribute name for Group/Role', 'miniorange-saml-20-single-sign-on' ); ?>" class="mo-saml-bootstrap-w-100 mo-saml-bootstrap-bg-light cursor-disabled" value="" disabled>
				</div>
			</div>
			<div class="mo-saml-bootstrap-row align-items-top mo-saml-bootstrap-mt-4">
				<div class="mo-saml-bootstrap-col-md-3">
					<h6 class="mo-saml-bootstrap-text-secondary"><?php esc_html_e( 'Map Custom Attributes', 'miniorange-saml-20-single-sign-on' ); ?></h6>
				</div>
				<div class="mo-saml-bootstrap-col-md-6">
					<p class="mo-saml-bootstrap-mt-0"><?php esc_html_e( 'Customized Attribute Mapping means you can map any attribute of the IDP to the usermeta table of your database.', 'miniorange-saml-20-single-sign-on' ); ?></p>
				</div>
			</div>

		</div>
		<div class="align-items-top mo-saml-bootstrap-mt-3 prem-info">
			<div class="prem-icn anonymous-prem-img sso-btn-prem-img"><svg class="crown_img" xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 520 480" fill="none">
<path d="M384.994 138.665L392.555 153.785C403.316 175.336 392.945 201.949 365.082 209.696C364.73 209.813 326.138 220.172 314.417 185.066L303.12 151.133L335.267 118.972L255.941 0L176.616 118.972L208.763 151.133L197.466 185.066C184.538 223.8 131.333 214.681 118.786 187.367C113.834 176.612 114.039 164.379 119.328 153.785L126.874 138.679L0 93.3903L60.8969 327.116L90.9038 357.123H255.941H420.979L450.986 327.116L512 93.4053L384.994 138.665Z" fill="#FED843"/>
<path d="M450.986 327.116L512 93.4053L384.994 138.665L392.555 153.785C403.316 175.336 392.945 201.949 365.082 209.696C364.73 209.813 326.137 220.172 314.417 185.066L303.12 151.133L335.267 118.972L255.941 0V357.123H420.979L450.986 327.116Z" fill="#FABE2C"/>
<path d="M255.942 327.116H60.897V402.133H255.942H450.986V327.116H255.942Z" fill="#FABE2C"/>
<path d="M255.941 327.116H450.986V402.133H255.941V327.116Z" fill="#FF9100"/>
<path d="M180.867 327.103L255.873 252.097L330.879 327.103L255.873 402.108L180.867 327.103Z" fill="#FABE2C"/>
<path d="M255.941 252.099V402.133L330.959 327.116L255.941 252.099Z" fill="#FF9100"/>
				<p class="prem-info-text"><?php esc_html_e( 'Enable this option if you want to allow users to login to the WordPress site without creating a WordPress user account for them.', 'miniorange-saml-20-single-sign-on' ); ?><a href="<?php echo esc_url( Mo_Saml_External_Links::LANDING_PAGE . '?utm_source=saml_plugin&utm_medium=upgrade_attribute&utm_campaign=saml_plugin_internal#pricing' ); ?>" target="_blank" class="mo-saml-bootstrap-text-warning"><?php esc_html_e( 'Click here to Upgrade', 'miniorange-saml-20-single-sign-on' ); ?></a></p>
			</div>
			<div class="mo-saml-bootstrap-row mo-saml-bootstrap-align-items-center">
				<div class="mo-saml-bootstrap-col-md-3">
					<h6 class="mo-saml-bootstrap-text-secondary"><?php esc_html_e( 'Anonymous Login:', 'miniorange-saml-20-single-sign-on' ); ?> </h6>
				</div>
				<div class="mo-saml-bootstrap-col-md-8">
					<section>
						<input type="checkbox" id="switch" class="mo-saml-switch" disabled /><label class="mo-saml-switch-label cursor-disabled" for="switch"><?php esc_html_e( 'Toggle', 'miniorange-saml-20-single-sign-on' ); ?></label>
					</section>
				</div>
			</div>
		</div>
	<?php
}

/**
 * Function to Display Role Mapping.
 *
 * @param string $default_role it is the default role of the user.
 * @param mixed  $roles retrieves the list of WP role names.
 * @return void
 */
function mo_saml_display_role_mapping( $default_role, $roles ) {
	?>
	<div class="mo-saml-bootstrap-row align-items-top">
			<div class="mo-saml-bootstrap-col-md-12">
				<h4 class="form-head">
					<span class="entity-info"><?php esc_html_e( 'Role Mapping', 'miniorange-saml-20-single-sign-on' ); ?>
						<a href="https://developers.miniorange.com/docs/saml/wordpress/role-mapping?utm_source=saml_plugin&utm_medium=role_mapping&utm_campaign=saml_plugin_internal" class="mo-saml-bootstrap-text-dark" target="_blank">
							<svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
								<path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z" />
								<path d="M5.255 5.786a.237.237 0 0 0 .241.247h.825c.138 0 .248-.113.266-.25.09-.656.54-1.134 1.342-1.134.686 0 1.314.343 1.314 1.168 0 .635-.374.927-.965 1.371-.673.489-1.206 1.06-1.168 1.987l.003.217a.25.25 0 0 0 .25.246h.811a.25.25 0 0 0 .25-.25v-.105c0-.718.273-.927 1.01-1.486.609-.463 1.244-.977 1.244-2.056 0-1.511-1.276-2.241-2.673-2.241-1.267 0-2.655.59-2.75 2.286zm1.557 5.763c0 .533.425.927 1.01.927.609 0 1.028-.394 1.028-.927 0-.552-.42-.94-1.029-.94-.584 0-1.009.388-1.009.94z" />
							</svg>
						</a>
					</span>
				</h4>
			</div>
		</div>
		<br>
	<form name="saml_form_am_role_mapping" method="post" action="">
		<?php
		wp_nonce_field( 'login_widget_saml_role_mapping' );
		?>
		<input type="hidden" name="option" value="login_widget_saml_role_mapping" />
		
		<div class="mo-saml-bootstrap-row mo-saml-bootstrap-align-items-center mo-saml-bootstrap-mt-0">
			<div class="mo-saml-bootstrap-col-md-3" style="margin-right: 1rem;font-size: 1.1rem;">
			<b><?php esc_html_e( 'Default Role :', 'miniorange-saml-20-single-sign-on' ); ?></b>
			</div>
			<div class="mo-saml-bootstrap-col-md-2">
				<select id="saml_am_default_user_role" name="saml_am_default_user_role">
					<?php
					wp_dropdown_roles( $default_role );
					?>
				</select>
			</div>
			<div class="mo-saml-bootstrap-col-md-2">
				<input type="submit" class="btn-cstm mo-saml-bootstrap-bg-info mo-saml-bootstrap-rounded" name="submit" value="<?php esc_html_e( 'Update', 'miniorange-saml-20-single-sign-on' ); ?>">
			</div>
			<div class="mo-saml-bootstrap-align-items-center mo-saml-bootstrap-mt-3"><b><?php esc_html_e( 'NOTE:', 'miniorange-saml-20-single-sign-on' ); ?></b> <?php esc_html_e( 'Role will be assigned only to new users. Existing WordPress users\' role remains same.', 'miniorange-saml-20-single-sign-on' ); ?></div>
			
		</div>

			<div class="prem-info" >
				<div class="prem-icn role-prem-img sso-btn-prem-img" style="display: flex; justify-content: flex-end;"><svg class="crown_img" xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 520 480" fill="none">
<path d="M384.994 138.665L392.555 153.785C403.316 175.336 392.945 201.949 365.082 209.696C364.73 209.813 326.138 220.172 314.417 185.066L303.12 151.133L335.267 118.972L255.941 0L176.616 118.972L208.763 151.133L197.466 185.066C184.538 223.8 131.333 214.681 118.786 187.367C113.834 176.612 114.039 164.379 119.328 153.785L126.874 138.679L0 93.3903L60.8969 327.116L90.9038 357.123H255.941H420.979L450.986 327.116L512 93.4053L384.994 138.665Z" fill="#FED843"/>
<path d="M450.986 327.116L512 93.4053L384.994 138.665L392.555 153.785C403.316 175.336 392.945 201.949 365.082 209.696C364.73 209.813 326.137 220.172 314.417 185.066L303.12 151.133L335.267 118.972L255.941 0V357.123H420.979L450.986 327.116Z" fill="#FABE2C"/>
<path d="M255.942 327.116H60.897V402.133H255.942H450.986V327.116H255.942Z" fill="#FABE2C"/>
<path d="M255.941 327.116H450.986V402.133H255.941V327.116Z" fill="#FF9100"/>
<path d="M180.867 327.103L255.873 252.097L330.879 327.103L255.873 402.108L180.867 327.103Z" fill="#FABE2C"/>
<path d="M255.941 252.099V402.133L330.959 327.116L255.941 252.099Z" fill="#FF9100"/>
					<p class="prem-info-text"><?php esc_html_e( 'Customized Role Mapping options are configurable in the Premium, Enterprise and All-Inclusive versions of the plugin.', 'miniorange-saml-20-single-sign-on' ); ?> <a href="<?php echo esc_url( Mo_Saml_External_Links::LANDING_PAGE . '?utm_source=saml_plugin&utm_medium=upgrade_role&utm_campaign=saml_plugin_internal#pricing' ); ?>" target="_blank" class="mo-saml-bootstrap-text-warning"><?php esc_html_e( 'Click here to upgrade', 'miniorange-saml-20-single-sign-on' ); ?></a></p>
				</div>
				<div class="mo-saml-bootstrap-row align-items-top mo-saml-bootstrap-mt-0 mo-saml-bootstrap-mb-4 mo-saml-bootstrap-col-md-12">
					<div class="mo-saml-bootstrap-col-md-8">
						<h6 class="mo-saml-bootstrap-text-secondary" style="display: flex; align-items: center;">
							<span><?php esc_html_e( 'Do not auto create users if roles are not mapped here', 'miniorange-saml-20-single-sign-on' ); ?></span>
						</h6>
						<p class="prem-note"><i><?php esc_html_e( 'Enable this option if you do not want the unmapped users to register into your site via SSO.', 'miniorange-saml-20-single-sign-on' ); ?></i></p>
					</div>
					<div class="mo-saml-bootstrap-col-md-4">
						<input type="checkbox" id="switch" class="mo-saml-switch" disabled /><label class="mo-saml-switch-label cursor-disabled" for="switch"><?php esc_html_e( 'Toggle', 'miniorange-saml-20-single-sign-on' ); ?></label>
					</div>
				</div>
				<div class="mo-saml-bootstrap-row mo-saml-bootstrap-col-md-12">
					<div class="mo-saml-bootstrap-col-md-8">
						<h6 class="mo-saml-bootstrap-text-secondary" style="display: flex; align-items: center;">
							<span><?php esc_html_e( 'Do not assign role to unlisted users', 'miniorange-saml-20-single-sign-on' ); ?></span>
						</h6>
						<p class="prem-note"><i><?php esc_html_e( 'Enable this option if you do not want to assign any roles to unmapped users.', 'miniorange-saml-20-single-sign-on' ); ?></i></p>
					</div>
					<div class="mo-saml-bootstrap-col-md-4">
						<input type="checkbox" id="switch" class="mo-saml-switch" disabled /><label class="mo-saml-switch-label cursor-disabled" for="switch"><?php esc_html_e( 'Toggle', 'miniorange-saml-20-single-sign-on' ); ?></label>
					</div>
				</div>
			</div>

			<div class="mo-saml-bootstrap-d-block prem-info mo-saml-bootstrap-mt-3">
				<div class="prem-icn role-admin-prem-img sso-btn-prem-img"><svg class="crown_img" xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 520 480" fill="none">
<path d="M384.994 138.665L392.555 153.785C403.316 175.336 392.945 201.949 365.082 209.696C364.73 209.813 326.138 220.172 314.417 185.066L303.12 151.133L335.267 118.972L255.941 0L176.616 118.972L208.763 151.133L197.466 185.066C184.538 223.8 131.333 214.681 118.786 187.367C113.834 176.612 114.039 164.379 119.328 153.785L126.874 138.679L0 93.3903L60.8969 327.116L90.9038 357.123H255.941H420.979L450.986 327.116L512 93.4053L384.994 138.665Z" fill="#FED843"/>
<path d="M450.986 327.116L512 93.4053L384.994 138.665L392.555 153.785C403.316 175.336 392.945 201.949 365.082 209.696C364.73 209.813 326.137 220.172 314.417 185.066L303.12 151.133L335.267 118.972L255.941 0V357.123H420.979L450.986 327.116Z" fill="#FABE2C"/>
<path d="M255.942 327.116H60.897V402.133H255.942H450.986V327.116H255.942Z" fill="#FABE2C"/>
<path d="M255.941 327.116H450.986V402.133H255.941V327.116Z" fill="#FF9100"/>
<path d="M180.867 327.103L255.873 252.097L330.879 327.103L255.873 402.108L180.867 327.103Z" fill="#FABE2C"/>
<path d="M255.941 252.099V402.133L330.959 327.116L255.941 252.099Z" fill="#FF9100"/>
					<p class="prem-info-text"><?php esc_html_e( 'Customized Role Mapping options are configurable in the Premium, Enterprise and All-Inclusive versions of the plugin. ', 'miniorange-saml-20-single-sign-on' ); ?><a href="<?php echo esc_url( Mo_Saml_External_Links::LANDING_PAGE . '?utm_source=saml_plugin&utm_medium=upgrade_role&utm_campaign=saml_plugin_internal#pricing' ); ?>" target="_blank" class="mo-saml-bootstrap-text-warning"><?php esc_html_e( 'Click here to upgrade', 'miniorange-saml-20-single-sign-on' ); ?></a></p>
				</div>
				<?php
				foreach ( $roles as $role_value => $role_name ) {
					?>
					<div class="mo-saml-bootstrap-row align-items-top mo-saml-bootstrap-mt-3 mo-saml-bootstrap-mb-3">
						<div class="mo-saml-bootstrap-col-md-3">
							<h6 class="mo-saml-bootstrap-text-secondary"><?php echo esc_html( $role_name ); ?> :</h6>
						</div>
						<div class="mo-saml-bootstrap-col-md-7">
							<?php
							// translators: %s: WordPress role name.
							$placeholder_text = sprintf( __( 'Semi-colon(;) separated Group/Role value for %s', 'miniorange-saml-20-single-sign-on' ), $role_name );
							?>
							<input type="text" name="" placeholder="<?php echo esc_attr( $placeholder_text ); ?>" class="mo-saml-bootstrap-w-100 mo-saml-bootstrap-bg-light cursor-disabled" value="" disabled>
						</div>
					</div>
					<?php
				}
				?>
			</div>
	</form>

	<?php
}

/**
 * Function to display user attributes sent by the identity provider.
 *
 * @return void
 */
function mo_saml_display_attrs_list() {
	$idp_attrs = get_option( Mo_Saml_Options_Test_Configuration::TEST_CONFIG_ATTRS );
	$idp_attrs = maybe_unserialize( $idp_attrs );
	if ( ! empty( $idp_attrs ) ) {
		?>
		<div class="mo-saml-bootstrap-bg-white mo-saml-bootstrap-text-center shadow-cstm mo-saml-bootstrap-rounded contact-form-cstm mo-saml-bootstrap-p-4">
			<h4><?php esc_html_e( 'Attributes sent by the Identity Provider', 'miniorange-saml-20-single-sign-on' ); ?>:</h4>
			<div>
				<table style="table-layout: fixed;border: 1px solid #d5d5d5;border-bottom:none;width: 100%; border-radius:8px;">
					<tr style="text-align:center;background:#e9f0ff">
						<td style="font-weight:500; border:1.5px solid #fff;padding:2%;	word-wrap:break-word;border-radius:8px; font-size:15px"><?php esc_html_e( 'ATTRIBUTE NAME', 'miniorange-saml-20-single-sign-on' ); ?></td>
						<td style="font-weight:500; border:1.5px solid #fff;padding:2%;	word-wrap:break-word;border-radius:8px; font-size:15px"><?php esc_html_e( 'ATTRIBUTE VALUE', 'miniorange-saml-20-single-sign-on' ); ?></td>
					</tr>
					<?php
					foreach ( $idp_attrs as $attr_name => $values ) {
						if ( is_array( $values ) ) {
							$attr_values = implode( '<hr>', $values );
						} else {
							$attr_values = esc_html( $values );
						}
						$allowed_html = array( 'hr' => array() );
						?>
						<tr style="text-align:center;">
							<td style="font-weight:500; border-bottom:1.5px solid #cac9c9;padding:2%;word-wrap:break-word;font-size:13px;"> <?php echo esc_html( $attr_name ); ?></td>
							<td style="font-weight:500; border-bottom:1.5px solid #cac9c9;border-left:1.5px solid #cac9c9;padding:2%;	word-wrap:break-word;font-size:13px;"> <?php echo wp_kses( $attr_values, $allowed_html ); ?> </td>
						</tr>
					<?php } ?>

				</table>
				<br />
				<p style="text-align:center;"><input type="button" class="btn-cstm mo-saml-bootstrap-rounded mo-saml-bootstrap-mt-3" value="<?php echo esc_attr_x( 'Clear Attributes List', '', 'miniorange-saml-20-single-sign-on' ); ?>" onclick="document.forms['attrs_list_form'].submit();"></p>
				<div style="padding-right:8px;">
					<p><b><?php esc_html_e( 'NOTE', 'miniorange-saml-20-single-sign-on' ); ?> :</b> <?php esc_html_e( 'Please clear this list after configuring the plugin to hide your confidential attributes.', 'miniorange-saml-20-single-sign-on' ); ?><br />
						<?php esc_html_e( 'Click on Test configuration in Service Provider Setup tab to populate the list again.', 'miniorange-saml-20-single-sign-on' ); ?></p>
				</div>
				<form method="post" action="" id="attrs_list_form">
					<?php wp_nonce_field( 'clear_attrs_list' ); ?>
					<input type="hidden" name="option" value="clear_attrs_list">
				</form>
			</div>
		</div> 
		<?php
	}
}
