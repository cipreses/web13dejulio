/**
 * WordPress Command Palette integration for miniOrange SAML SSO Plugin
 *
 * This file registers commands for the WordPress Command Palette (WordPress 6.3+)
 * Commands can be accessed via Ctrl+K (or Cmd+K on Mac) in the WordPress admin.
 *
 * @package miniorange-saml-20-single-sign-on
 */

(function() {
	'use strict';

	if (typeof wp === 'undefined' || typeof wp.data === 'undefined') {
		return;
	}

	const commandsStore = wp.data.dispatch('core/commands');
	if (!commandsStore || typeof commandsStore.registerCommand !== 'function') {
		return;
	}

	const { __ } = wp.i18n || { __: function(text) { return text; } };

	const adminUrl = typeof moSamlCommandPalette !== 'undefined' ? moSamlCommandPalette.adminUrl : '/wp-admin/';
	const siteUrl = typeof moSamlCommandPalette !== 'undefined' ? moSamlCommandPalette.siteUrl : '/';

	const createCallback = (url) => {
		return function({ close }) {
			window.location.href = url;
			if (close && typeof close === 'function') {
				close();
			}
		};
	};

	const createNewWindowCallback = (url) => {
		return function({ close }) {
			// Open test configuration in a new window, matching plugin's behavior
			window.open(url, 'TEST SAML IDP', 'scrollbars=1,width=800,height=600');
			if (close && typeof close === 'function') {
				close();
			}
		};
	};


	const createToggleSSOButtonCallback = (action) => {
		return function({ close }) {
			const ajaxUrl = typeof moSamlCommandPalette !== 'undefined' && moSamlCommandPalette.ajaxUrl ? moSamlCommandPalette.ajaxUrl : (typeof ajaxurl !== 'undefined' ? ajaxurl : adminUrl + 'admin-ajax.php');
			
			fetch(ajaxUrl, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded',
				},
				body: new URLSearchParams({
					action: 'mo_saml_get_toggle_sso_nonce',
				}),
			})
			.then(response => response.json())
			.then(data => {
				if (data.success && data.data && data.data.nonce) {
					return fetch(ajaxUrl, {
						method: 'POST',
						headers: {
							'Content-Type': 'application/x-www-form-urlencoded',
						},
						body: new URLSearchParams({
							action: 'mo_saml_toggle_sso_button',
							action_type: action,
							nonce: data.data.nonce,
						}),
					});
				} else {
					throw new Error('Failed to get nonce');
				}
			})
			.then(response => response.json())
			.then(data => {
				if (data.success) {
					window.location.href = adminUrl + 'admin.php?page=mo_saml_settings&tab=sso-links';
					if (close && typeof close === 'function') {
						close();
					}
				} else {
					window.location.href = adminUrl + 'admin.php?page=mo_saml_settings&tab=sso-links';
					if (close && typeof close === 'function') {
						close();
					}
				}
			})
			.catch(error => {
				window.location.href = adminUrl + 'admin.php?page=mo_saml_settings&tab=sso-links';
				if (close && typeof close === 'function') {
					close();
				}
			});
		};
	};

	// Register all commands
	const commands = [
		{
			name: 'mo-saml/service-provider-metadata',
			label: __('miniOrange SAML SSO -> Service Provider Metadata', 'miniorange-saml-20-single-sign-on'),
			callback: createCallback(adminUrl + 'admin.php?page=mo_saml_settings&tab=config'),
		},
		{
			name: 'mo-saml/idp-configuration',
			label: __('miniOrange SAML SSO -> IDP Configuration', 'miniorange-saml-20-single-sign-on'),
			callback: createCallback(adminUrl + 'admin.php?page=mo_saml_settings&tab=save'),
		},
		{
			name: 'mo-saml/attribute-role-mapping',
			label: __('miniOrange SAML SSO -> Attribute/Role Mapping', 'miniorange-saml-20-single-sign-on'),
			callback: createCallback(adminUrl + 'admin.php?page=mo_saml_settings&tab=role'),
		},
		{
			name: 'mo-saml/redirection-sso-links',
			label: __('miniOrange SAML SSO -> Redirection & SSO Links', 'miniorange-saml-20-single-sign-on'),
			callback: createCallback(adminUrl + 'admin.php?page=mo_saml_settings&tab=sso-links'),
		},
		{
			name: 'mo-saml/test-configuration',
			label: __('miniOrange SAML SSO -> Test Configuration', 'miniorange-saml-20-single-sign-on'),
			callback: createNewWindowCallback(siteUrl + '?option=testConfig'),
		},
		{
			name: 'mo-saml/debug-logs',
			label: __('miniOrange SAML SSO -> Debug Logs', 'miniorange-saml-20-single-sign-on'),
			callback: createCallback(adminUrl + 'admin.php?page=mo_saml_enable_debug_logs&tab=debug-logs'),
		},
		{
			name: 'mo-saml/enable-sso-button',
			label: __('miniOrange SAML SSO -> Add SSO on WP Login Page', 'miniorange-saml-20-single-sign-on'),
			callback: createToggleSSOButtonCallback('enable'),
		},
		{
			name: 'mo-saml/disable-sso-button',
			label: __('miniOrange SAML SSO -> Remove SSO on WP Login Page', 'miniorange-saml-20-single-sign-on'),
			callback: createToggleSSOButtonCallback('disable'),
		},
	];

	commands.forEach(function(command) {
		try {
			commandsStore.registerCommand(command);
		} catch (error) {
		}
	});

})();

