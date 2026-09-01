<?php
/**
 * Register Abilities handler File
 *
 * @category Register abilities handler
 * @package miniorange-saml-20-single-sign-on\handlers
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once dirname( __DIR__ ) . '/class-mo-saml-utilities.php';
require_once dirname( __DIR__ ) . '/includes/lib/class-mo-saml-options-enum.php';
require_once dirname( __DIR__ ) . '/mo-saml-import-export.php';
require_once dirname( __DIR__ ) . '/views/mo-saml-abilities-api.php';
require_once dirname( __DIR__ ) . '/handlers/class-mo-saml-post-save-handler.php';

/**
 * Class to register abilities.
 */
class Mo_SAML_Register_Abilities {

	/**
	 * Register the MO SAML SSO ability category.
	 */
	public static function mo_saml_register_ability_category() {
		wp_register_ability_category(
			'mo-saml-sso',
			array(
				'label'       => 'SAML SSO',
				'description' => 'miniOrange SAML Single Sign-On configuration and status abilities',
			)
		);
	}

	/**
	 * Register all abilities.
	 * Only registers abilities if the Abilities API is enabled.
	 */
	public static function mo_saml_register_all_abilities() {
		$abilities_api_enabled = get_option( Mo_Saml_Options_Enum_Sso_Login::MO_SAML_ENABLE_ABILITIES_API );
		if ( 'true' !== $abilities_api_enabled ) {
			Mo_SAML_Utilities::mo_saml_unregister_abilities_api_array();
			return;
		}

		self::mo_saml_get_idp_name();
		self::mo_saml_fix_certificate_mismatch();
		self::mo_saml_fix_entity_id();
		self::mo_saml_fix_iconv_cert();
		self::mo_saml_update_default_role();
		self::mo_saml_show_sso_configurations();
		self::mo_saml_get_idp_guide_links();
		self::mo_saml_enable_sso_button();
		self::mo_saml_disable_sso_button();
		self::mo_saml_send_support_request();
		self::mo_saml_get_sp_metadata();
	}

	/**
	 * Register the ability to get the IDP name.
	 */
	public static function mo_saml_get_idp_name() {
		wp_register_ability(
			'mo-saml/get-idp-name',
			array(
				'label'               => 'Get IDP Name',
				'description'         => 'Fetch the name of the connected Identity Provider (IDP)',
				'category'            => 'mo-saml-sso',

				'input_schema'        => array(
					'type'                 => 'object',
					'properties'           => array(),
					'additionalProperties' => false,
					'default'              => array(),
				),

				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'success'  => array( 'type' => 'boolean' ),
						'message'  => array( 'type' => 'string' ),
						'idp_name' => array( 'type' => array( 'string', 'null' ) ),
					),
					'required'   => array( 'success', 'message', 'idp_name' ),
				),

				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},

				'execute_callback'    => function () {
					$idp_name = get_option(
						Mo_Saml_Options_Enum_Service_Provider::IDENTITY_NAME
					);

					return array(
						'success'  => true,
						'message'  => 'IDP name retrieved successfully.',
						'idp_name' => $idp_name ? $idp_name : null,
					);
				},

				'meta'                => array_merge(
					array(
						'show_in_rest' => true,
						'annotations'  => array(
							'readonly'      => true,
							'idempotent'    => true,
							'openWorldHint' => true,
						),
					),
					self::mo_saml_get_mcp_public_setting() ? array( 'mcp' => self::mo_saml_get_mcp_public_setting() ) : array()
				),
			)
		);
	}

	/**
	 * Register the ability to fix the certificate mismatch.
	 */
	public static function mo_saml_fix_certificate_mismatch() {
		wp_register_ability(
			'mo-saml/fix-wpsamlerr004',
			array(
				'label'               => 'Fix Certificate Mismatch WPSAMLERR004',
				'description'         => 'Fix the certificate mismatch in the plugin',
				'category'            => 'mo-saml-sso',
				'input_schema'        => array(
					'type'                 => 'object',
					'properties'           => array(),
					'additionalProperties' => false,
					'default'              => array(),
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'success' => array( 'type' => 'boolean' ),
						'message' => array( 'type' => 'string' ),
					),
					'required'   => array( 'success', 'message' ),
				),
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
				'execute_callback'    => function () {
					$saml_required_certificate = get_option( Mo_Saml_Sso_Constants::MO_SAML_REQUIRED_CERTIFICATE );
					$saml_certificate          = maybe_unserialize( get_option( Mo_Saml_Options_Enum_Service_Provider::X509_CERTIFICATE ) );
					if ( $saml_required_certificate === $saml_certificate[0] ) {
						return array(
							'success' => false,
							'message' => 'There is no certificate mismatch WPSAMLERR004.',
						);
					}
					$saml_certificate[0] = Mo_SAML_Utilities::mo_saml_sanitize_certificate( $saml_required_certificate );
					update_option( Mo_Saml_Options_Enum_Service_Provider::X509_CERTIFICATE, $saml_certificate );
					return array(
						'success' => true,
						'message' => 'Certificate mismatch WPSAMLERR004 fixed successfully.',
					);
				},
				'meta'                => array_merge(
					array(
						'show_in_rest' => true,
						'annotations'  => array(
							'readonly'      => true,
							'idempotent'    => true,
							'openWorldHint' => true,
						),
					),
					self::mo_saml_get_mcp_public_setting() ? array( 'mcp' => self::mo_saml_get_mcp_public_setting() ) : array()
				),
			)
		);
	}


	/**
	 * Register the ability to fix the entity ID.
	 */
	public static function mo_saml_fix_entity_id() {
		wp_register_ability(
			'mo-saml/fix-wpsamlerr010',
			array(
				'label'               => 'Fix Entity ID WPSAMLERR010',
				'description'         => 'Fix the entity ID in the plugin',
				'category'            => 'mo-saml-sso',
				'input_schema'        => array(
					'type'                 => 'object',
					'properties'           => array(),
					'additionalProperties' => false,
					'default'              => array(),
				),
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'success' => array( 'type' => 'boolean' ),
						'message' => array( 'type' => 'string' ),
					),
					'required'   => array( 'success', 'message' ),
				),
				'execute_callback'    => function () {
					$saml_required_issuer = get_option( Mo_Saml_Sso_Constants::MO_SAML_REQUIRED_ISSUER );
					update_option( Mo_Saml_Options_Enum_Service_Provider::ISSUER, $saml_required_issuer );
					return array(
						'success' => true,
						'message' => 'Entity ID WPSAMLERR010 fixed successfully.',
					);
				},
				'meta'                => array_merge(
					array(
						'show_in_rest' => true,
						'annotations'  => array(
							'readonly'      => true,
							'idempotent'    => true,
							'openWorldHint' => true,
						),
					),
					self::mo_saml_get_mcp_public_setting() ? array( 'mcp' => self::mo_saml_get_mcp_public_setting() ) : array()
				),
			)
		);
	}

	/**
	 * Register the ability to fix the iconv certificate.
	 */
	public static function mo_saml_fix_iconv_cert() {
		wp_register_ability(
			'mo-saml/fix-wpsamlerr012',
			array(
				'label'               => 'Fix Iconv Certificate WPSAMLERR012',
				'description'         => 'Fix the iconv certificate in the plugin',
				'category'            => 'mo-saml-sso',
				'input_schema'        => array(
					'type'                 => 'object',
					'properties'           => array(),
					'additionalProperties' => false,
					'default'              => array(),
				),
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'success' => array( 'type' => 'boolean' ),
						'message' => array( 'type' => 'string' ),
					),
					'required'   => array( 'success', 'message' ),
				),
				'execute_callback'    => function () {
					update_option( Mo_Saml_Options_Enum_Service_Provider::IS_ENCODING_ENABLED, 'unchecked' );
					return array(
						'success' => true,
						'message' => 'Iconv certificate WPSAMLERR012 fixed successfully.',
					);
				},
				'meta'                => array_merge(
					array(
						'show_in_rest' => true,
						'annotations'  => array(
							'readonly'      => true,
							'idempotent'    => true,
							'openWorldHint' => true,
						),
					),
					self::mo_saml_get_mcp_public_setting() ? array( 'mcp' => self::mo_saml_get_mcp_public_setting() ) : array()
				),
			)
		);
	}

	/**
	 * Register the ability to update the default role.
	 */
	public static function mo_saml_update_default_role() {
		wp_register_ability(
			'mo-saml/update-default-role',
			array(
				'label'               => 'Update Default Role',
				'description'         => 'Update the default role in the plugin',
				'category'            => 'mo-saml-sso',
				'input_schema'        => array(
					'type'                 => 'object',
					'properties'           => array(
						'default_role' => array(
							'type'        => 'string',
							'description' => 'The default role to update',
						),
					),
					'additionalProperties' => false,
					'default'              => array(
						'default_role' => 'subscriber',
					),
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'success' => array( 'type' => 'boolean' ),
						'message' => array( 'type' => 'string' ),
					),
					'required'   => array( 'success', 'message' ),
				),
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
				'execute_callback'    => function ( $input ) {
					$default_role = $input['default_role'];
					if ( empty( $default_role ) ) {
						return array(
							'success' => false,
							'message' => 'Default role is required.',
						);
					}
					if ( ! in_array( $default_role, array_keys( wp_roles()->roles ), true ) ) {
						return array(
							'success' => false,
							'message' => 'Invalid default role.',
						);
					}
					$default_role        = strtolower( sanitize_text_field( $default_role ) );
					$default_role_config = get_option( Mo_Saml_Options_Enum_Role_Mapping::ROLE_DEFAULT_ROLE );
					if ( $default_role_config === $default_role ) {
						return array(
							'success'      => false,
							'message'      => 'Default role is already set to this role.',
							'default_role' => $default_role,
						);
					}
					update_option( Mo_Saml_Options_Enum_Role_Mapping::ROLE_DEFAULT_ROLE, $default_role );
					return array(
						'success'      => true,
						'message'      => 'Default role updated successfully.',
						'default_role' => $default_role,
					);
				},
				'meta'                => array_merge(
					array(
						'show_in_rest' => true,
						'annotations'  => array(
							'readonly'      => true,
							'idempotent'    => true,
							'openWorldHint' => true,
						),
					),
					self::mo_saml_get_mcp_public_setting() ? array( 'mcp' => self::mo_saml_get_mcp_public_setting() ) : array()
				),
			)
		);
	}

	/**
	 * Register the ability to show the SSO configurations.
	 */
	public static function mo_saml_show_sso_configurations() {
		wp_register_ability(
			'mo-saml/show-sso-configurations',
			array(
				'label'               => 'Show SSO Configurations',
				'description'         => 'Show the SAML plugin configurations',
				'category'            => 'mo-saml-sso',
				'input_schema'        => array(
					'type'                 => 'object',
					'properties'           => array(),
					'additionalProperties' => false,
					'default'              => array(),
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'success'             => array( 'type' => 'boolean' ),
						'message'             => array( 'type' => 'string' ),
						'configuration_array' => array(
							'type'                 => 'object',
							'description'          => 'Configuration data organized by category',
							'additionalProperties' => true,
						),
					),
					'required'   => array( 'success', 'message', 'configuration_array' ),
				),
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
				'execute_callback'    => function () {
					if ( ! defined( 'MO_SAML_TAB_CLASS_NAMES' ) ) {
						$mo_saml_tab_class_names_array = array(
							'SSO_Login'         => 'Mo_Saml_Options_Enum_Sso_Login',
							'Identity_Provider' => 'Mo_Saml_Options_Enum_Identity_Provider',
							'Service_Provider'  => 'Mo_Saml_Options_Enum_Service_Provider',
							'Attribute_Mapping' => 'Mo_Saml_Options_Enum_Attribute_Mapping',
							'Role_Mapping'      => 'Mo_Saml_Options_Enum_Role_Mapping',
						);

						if ( get_option( Mo_Saml_Sso_Constants::MO_SAML_TEST_STATUS ) !== 1 ) {
							$mo_saml_tab_class_names_array['Test_Configuration'] = 'Mo_Saml_Options_Test_Configuration';
						}

						define( 'MO_SAML_TAB_CLASS_NAMES', maybe_serialize( $mo_saml_tab_class_names_array ) );
					}

					$tab_class_name_raw  = maybe_unserialize( MO_SAML_TAB_CLASS_NAMES );
					$tab_class_name      = is_array( $tab_class_name_raw ) ? $tab_class_name_raw : array();
					$configuration_array = array();

					foreach ( $tab_class_name as $key => $value ) {
						if ( is_string( $value ) && class_exists( $value ) ) {
							$config_result               = mo_saml_get_configuration_array( $value );
							$configuration_array[ $key ] = is_array( $config_result ) ? $config_result : array();
						} else {
							$configuration_array[ $key ] = array();
						}
					}

					$version_info                                = mo_saml_get_version_informations();
					$configuration_array['Version_dependencies'] = is_array( $version_info ) ? $version_info : array();

					// Ensure configuration_array is always a valid associative array (object).
					if ( ! is_array( $configuration_array ) ) {
						$configuration_array = array();
					}
					return array(
						'success'             => true,
						'message'             => 'SSO configurations retrieved successfully.',
						'configuration_array' => $configuration_array,
					);
				},
				'meta'                => array_merge(
					array(
						'show_in_rest' => true,
						'annotations'  => array(
							'readonly'      => true,
							'idempotent'    => false,
							'openWorldHint' => true,
						),
					),
					self::mo_saml_get_mcp_public_setting() ? array( 'mcp' => self::mo_saml_get_mcp_public_setting() ) : array()
				),
			)
		);
	}

	/**
	 * Register the ability to get the IDP guide links.
	 */
	public static function mo_saml_get_idp_guide_links() {
		wp_register_ability(
			'mo-saml/get-idp-guide-links',
			array(
				'label'               => 'Get IDP Guide Links',
				'description'         => 'Get the IDP guide links in the plugin',
				'category'            => 'mo-saml-sso',
				'input_schema'        => array(
					'type'                 => 'object',
					'properties'           => array(
						'idps' => array(
							'type'        => 'array',
							'description' => 'List of IDP names to fetch guides for',
							'items'       => array(
								'type' => 'string',
							),
						),
					),
					'additionalProperties' => false,
					'default'              => array(
						'idps' => array(),
					),
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'success'         => array( 'type' => 'boolean' ),
						'message'         => array( 'type' => 'string' ),
						'idp_guide_links' => array(
							'type'                 => 'object',
							'description'          => 'Object with IDP names as keys and guide link information as values',
							'additionalProperties' => array(
								'type'       => 'object',
								'properties' => array(
									'guide_url' => array( 'type' => 'string' ),
								),
							),
						),
					),
					'required'   => array( 'success', 'message', 'idp_guide_links' ),
				),
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
				'execute_callback'    => function ( $input ) {

					$all_guides     = Mo_Saml_Options_Plugin_Idp::$idp_guides;
					$requested_idps = $input['idps'] ?? array();

					$requested_idps  = array_map( 'strtolower', $requested_idps );
					$filtered_guides = array_filter(
						$all_guides,
						function ( $key ) use ( $requested_idps ) {
							return in_array( strtolower( $key ), $requested_idps, true );
						},
						ARRAY_FILTER_USE_KEY
					);
					$guide_links     = array();
					foreach ( $filtered_guides as $idp_name => $guide_data ) {
						if ( is_array( $guide_data ) && isset( $guide_data[1] ) && is_string( $guide_data[1] ) ) {
							$guide_links[ $idp_name ] = array(
								'guide_url' => 'https://plugins.miniorange.com/' . ltrim( $guide_data[1], '/' ),
							);
						}
					}
					if ( empty( $guide_links ) ) {
						$custom_idp_guide_url      = 'https://plugins.miniorange.com/saml-single-sign-on-sso-wordpress-using-custom-idp';
						$guide_links['Custom IDP'] = array(
							'guide_url' => $custom_idp_guide_url,
						);
						return array(
							'success'         => true,
							'message'         => 'Check Custom IDP guide for more information.',
							'idp_guide_links' => $guide_links,
						);
					}
					return array(
						'success'         => true,
						'message'         => 'Requested IDP guide links retrieved successfully.',
						'idp_guide_links' => $guide_links,
					);
				},
				'meta'                => array_merge(
					array(
						'show_in_rest' => true,
						'annotations'  => array(
							'readonly'      => true,
							'idempotent'    => true,
							'openWorldHint' => true,
						),
					),
					self::mo_saml_get_mcp_public_setting() ? array( 'mcp' => self::mo_saml_get_mcp_public_setting() ) : array()
				),
			)
		);
	}

	/**
	 * Register the ability to toggle the SSO button.
	 */
	public static function mo_saml_enable_sso_button() {
		wp_register_ability(
			'mo-saml/enable-sso-button',
			array(
				'label'               => 'Enable SSO Button',
				'description'         => 'Enable the SSO button in the plugin',
				'category'            => 'mo-saml-sso',
				'input_schema'        => array(
					'type'                 => 'object',
					'properties'           => array(),
					'additionalProperties' => false,
					'default'              => array(),
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'success'           => array( 'type' => 'boolean' ),
						'message'           => array( 'type' => 'string' ),
						'sso_button_status' => array( 'type' => 'string' ),
					),
					'required'   => array( 'success', 'message' ),
				),
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
				'execute_callback'    => function () {
					$sso_button_status = get_option( Mo_Saml_Options_Enum_Sso_Login::SSO_BUTTON );
					if ( 'false' === $sso_button_status ) {
						update_option( Mo_Saml_Options_Enum_Sso_Login::SSO_BUTTON, 'true' );
						return array(
							'success'           => true,
							'message'           => 'SSO button enabled successfully.',
							'sso_button_status' => 'true',
						);
					}
					return array(
						'success'           => false,
						'message'           => 'SSO button is already enabled.',
						'sso_button_status' => 'true',
					);
				},
				'meta'                => array_merge(
					array(
						'show_in_rest' => true,
						'annotations'  => array(
							'readonly'      => true,
							'idempotent'    => true,
							'openWorldHint' => true,
						),
					),
					self::mo_saml_get_mcp_public_setting() ? array( 'mcp' => self::mo_saml_get_mcp_public_setting() ) : array()
				),
			)
		);
	}

	/**
	 * Register the ability to disable the SSO button.
	 */
	public static function mo_saml_disable_sso_button() {
		wp_register_ability(
			'mo-saml/disable-sso-button',
			array(
				'label'               => 'Disable SSO Button',
				'description'         => 'Disable the SSO button in the plugin',
				'category'            => 'mo-saml-sso',
				'input_schema'        => array(
					'type'                 => 'object',
					'properties'           => array(),
					'additionalProperties' => false,
					'default'              => array(),
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'success'           => array( 'type' => 'boolean' ),
						'message'           => array( 'type' => 'string' ),
						'sso_button_status' => array( 'type' => 'string' ),
					),
					'required'   => array( 'success', 'message' ),
				),
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
				'execute_callback'    => function () {
					$sso_button_status = get_option( Mo_Saml_Options_Enum_Sso_Login::SSO_BUTTON );
					if ( 'true' === $sso_button_status ) {
						update_option( Mo_Saml_Options_Enum_Sso_Login::SSO_BUTTON, 'false' );
						return array(
							'success'           => true,
							'message'           => 'SSO button disabled successfully.',
							'sso_button_status' => 'false',
						);
					}
					return array(
						'success'           => false,
						'message'           => 'SSO button is already disabled.',
						'sso_button_status' => 'false',
					);
				},
				'meta'                => array_merge(
					array(
						'show_in_rest' => true,
						'annotations'  => array(
							'readonly'      => true,
							'idempotent'    => true,
							'openWorldHint' => true,
						),
					),
					self::mo_saml_get_mcp_public_setting() ? array( 'mcp' => self::mo_saml_get_mcp_public_setting() ) : array()
				),
			)
		);
	}

	/**
	 * Register the ability to send support request.
	 */
	public static function mo_saml_send_support_request() {
		wp_register_ability(
			'mo-saml/send-support-request',
			array(
				'label'               => 'Send Support Request',
				'description'         => 'Send a support request to the plugin',
				'category'            => 'mo-saml-sso',
				'input_schema'        => array(
					'type'                 => 'object',
					'properties'           => array(
						'email' => array(
							'type'        => 'string',
							'description' => 'The support request email',
						),
						'query' => array(
							'type'        => 'string',
							'description' => 'The support request query',
						),
					),
					'additionalProperties' => false,
					'default'              => array(
						'email' => '',
						'query' => '',
					),
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'success' => array( 'type' => 'boolean' ),
						'message' => array( 'type' => 'string' ),
					),
					'required'   => array( 'success', 'message' ),
				),
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
				'execute_callback'    => function ( $input ) {
					$email = $input['email'] ?? '';
					$query = $input['query'] ?? '';
					if ( empty( $email ) || empty( $query ) ) {
						return array(
							'success' => false,
							'message' => 'Email and query are required.',
						);
					}
					$response = ( new Mo_SAML_Customer() )->mo_saml_submit_contact_us( $email, '', $query, false );
					if ( is_null( $response ) || false === $response || 'Query submitted.' !== $response ) {
						return array(
							'success' => false,
							'message' => 'Failed to send support request.',
						);
					}
					return array(
						'success' => true,
						'method'  => 'POST',
						'message' => 'Support request sent successfully.',
					);
				},
				'meta'                => array_merge(
					array(
						'show_in_rest' => true,
						'annotations'  => array(
							'readonly'      => true,
							'idempotent'    => true,
							'openWorldHint' => true,
						),
					),
					self::mo_saml_get_mcp_public_setting() ? array( 'mcp' => self::mo_saml_get_mcp_public_setting() ) : array()
				),
			)
		);
	}

	/**
	 * Register the ability to get the SP metadata.
	 */
	public static function mo_saml_get_sp_metadata() {
		wp_register_ability(
			'mo-saml/get-sp-metadata',
			array(
				'label'               => 'Get SP Metadata',
				'description'         => 'Get the SP metadata in the plugin',
				'category'            => 'mo-saml-sso',
				'input_schema'        => array(
					'type'                 => 'object',
					'properties'           => array(),
					'additionalProperties' => false,
					'default'              => array(),
				),
				'output_schema'       => array(
					'type'       => 'object',
					'properties' => array(
						'success'     => array( 'type' => 'boolean' ),
						'message'     => array( 'type' => 'string' ),
						'sp_metadata' => array( 'type' => 'string' ),
					),
					'required'   => array( 'success', 'message', 'sp_metadata' ),
				),
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
				'execute_callback'    => function () {
					$sp_metadata = site_url() . '/?option=mosaml_metadata';
					return array(
						'success'     => true,
						'message'     => 'SP metadata retrieved successfully.',
						'sp_metadata' => $sp_metadata,
					);
				},
				'meta'                => array_merge(
					array(
						'show_in_rest' => true,
						'annotations'  => array(
							'readonly'      => true,
							'idempotent'    => true,
							'openWorldHint' => true,
						),
					),
					self::mo_saml_get_mcp_public_setting() ? array( 'mcp' => self::mo_saml_get_mcp_public_setting() ) : array()
				),
			)
		);
	}

	/**
	 * Display the Abilities API settings page.
	 *
	 * @return void
	 */
	public static function mo_saml_abilities_api_page() {
		mo_saml_display_abilities_api_page();
	}

	/**
	 * Get the MCP public setting based on the toggle state.
	 *
	 * @return array|false Returns array with public => true if enabled, false otherwise.
	 */
	private static function mo_saml_get_mcp_public_setting() {
		$abilities_api_enabled = get_option( Mo_Saml_Options_Enum_Sso_Login::MO_SAML_ENABLE_ABILITIES_API );
		if ( 'true' === $abilities_api_enabled ) {
			return array(
				'public' => true,
			);
		}
		return false;
	}

	/**
	 * Process the Abilities API toggle form submission.
	 *
	 * @param array $post_array The POST data array.
	 * @return void
	 */
	public static function mo_saml_process_abilities_api_toggle( $post_array ) {
		$mo_saml_enable_abilities_api = false;

		if ( version_compare( get_bloginfo( 'version' ), '6.8', '<' ) ) {
			update_option( Mo_Saml_Options_Enum_Sso_Login::MO_SAML_ENABLE_ABILITIES_API, 'false' );
			$post_save = new Mo_SAML_Post_Save_Handler( Mo_Saml_Save_Status_Constants::ERROR, Mo_Saml_Messages::mo_saml_translate( 'ABILITIES_API_NOT_SUPPORTED' ));
			$post_save->mo_saml_post_save_action();
			return;
		} else if ( ! class_exists( WP\MCP\Core\McpAdapter::class ) ) {
			update_option( Mo_Saml_Options_Enum_Sso_Login::MO_SAML_ENABLE_ABILITIES_API, 'false' );
			$post_save = new Mo_SAML_Post_Save_Handler( Mo_Saml_Save_Status_Constants::ERROR, Mo_Saml_Messages::mo_saml_translate( 'ABILITIES_API_NOT_SUPPORTED' ));
			$post_save->mo_saml_post_save_action();
			return;
		}

		if ( isset( $post_array['mo_saml_enable_abilities_api'] ) && 'true' === $post_array['mo_saml_enable_abilities_api'] ) {
			$mo_saml_enable_abilities_api = true;
		}

		if ( $mo_saml_enable_abilities_api ) {
			update_option( Mo_Saml_Options_Enum_Sso_Login::MO_SAML_ENABLE_ABILITIES_API, 'true' );
			$post_save = new Mo_SAML_Post_Save_Handler( Mo_Saml_Save_Status_Constants::SUCCESS, Mo_Saml_Messages::mo_saml_translate( 'ABILITIES_API_ENABLED' ));
		} else {
			update_option( Mo_Saml_Options_Enum_Sso_Login::MO_SAML_ENABLE_ABILITIES_API, 'false' );
			$post_save = new Mo_SAML_Post_Save_Handler( Mo_Saml_Save_Status_Constants::SUCCESS, Mo_Saml_Messages::mo_saml_translate( 'ABILITIES_API_DISABLED' ));
		}

		$post_save->mo_saml_post_save_action();
	}
}
