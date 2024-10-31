<?php
/** Read-More-Login plugin for WordPress.
 *  Puts a login/registration form in your posts and pages.
 *
 *  Copyright (C) 2018 Arild Hegvik
 *
 *  GNU GENERAL PUBLIC LICENSE (GNU GPLv3)
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace ARU_ReadMoreLogin;

defined( 'ABSPATH' ) || exit;

use WP_PluginFramework\PluginContainer;
use ARU_ReadMoreLogin\SettingsAdvancedOptions;

/*
 * This class is for internal testing and will download beta versions from www.readmorelogin.com.
 * This features is only for the plugin developer during release testing.
 *
 * Following symbols will enable licensed editions and pre-release downloads:
 *   READ_MORE_LOGIN_DOWNLOAD_PRE_RELEASE
 *   READ_MORE_LOGIN_DOWNLOAD_DEV_VERSION
 *   READ_MORE_LOGIN_ENTER_LICENSE_EDITION
  */

class CustomUpgrade {
	/* Transient for storing beta versions. */
	const ARI_READ_MORE_LOGIN_UPGRADE = 'ari_read-more-login_upgrade';

	static private function retreive_rml_version_data($options) {
		global $rml_custom_upgrade_result, $rml_custom_upgrade_message;

		$plugin                     = PluginContainer::Instance();
		$wp_version                 = get_bloginfo( 'version' );
		$url_to_read_more_login_com = 'https://www.readmorelogin.com/plugin-upgrade/api/1.0';

		$target_release = array();
		if ( defined( 'READ_MORE_LOGIN_DOWNLOAD_PRE_RELEASE' ) and ( READ_MORE_LOGIN_DOWNLOAD_PRE_RELEASE === true ) ) {
			if ( ( isset( $options[ SettingsAdvancedOptions::DOWNLOAD_RC_VERSIONS ] ) and ( $options[ SettingsAdvancedOptions::DOWNLOAD_RC_VERSIONS ] ) ) ) {
				$target_release[] = 'rc';
			}
			if ( ( isset( $options[ SettingsAdvancedOptions::DOWNLOAD_BETA_VERSIONS ] ) and ( $options[ SettingsAdvancedOptions::DOWNLOAD_BETA_VERSIONS ] ) ) ) {
				$target_release[] = 'beta';
			}
		}
		if ( defined( 'READ_MORE_LOGIN_DOWNLOAD_DEV_VERSION' ) and ( READ_MORE_LOGIN_DOWNLOAD_DEV_VERSION === true ) ) {
			if ( ( isset( $options[ SettingsAdvancedOptions::DOWNLOAD_DEV_VERSIONS ] ) and ( $options[ SettingsAdvancedOptions::DOWNLOAD_DEV_VERSIONS ] ) ) ) {
				$target_release[] = 'dev';
			}
		}

		$args                 = new \stdClass();
		$args->locale         = get_locale();
		$args->wp_version     = $wp_version;
		$args->slug           = 'read-more-login';
		$args->my_version     = $plugin->GetPluginVersion();
		$args->my_edition     = $plugin->GetPluginEdition();
		$args->license_key = '';
		$args->target_edition = $plugin->GetPluginEdition();
		if ( defined( 'READ_MORE_LOGIN_ENTER_LICENSE_EDITION' ) and ( READ_MORE_LOGIN_ENTER_LICENSE_EDITION === true ) ) {
			if ( isset($options[SettingsAdvancedOptions::LICENSE_KEY] ) ) {
				$license = sanitize_text_field($options[SettingsAdvancedOptions::LICENSE_KEY]);
				if($license) {
					$args->license_key    = $license;
					$args->target_edition = 'license';
					$target_release[]     = 'stable';
				}
			}
		}
		$args->target_release = $target_release;
		$args->home_url       = home_url( '/' );
		$serial_args = serialize( $args );

		$res_details = get_transient( self::ARI_READ_MORE_LOGIN_UPGRADE );

		if ( $res_details !== false ) {
			if($res_details->request_args !== $serial_args)
			{
				$res_details = false;
			}
		}

		if ( $res_details === false ) {
			$http_args = array(
				'timeout'    => 15,
				'user-agent' => 'WordPress/' . $wp_version . '; ' . home_url( '/' ),
				'body'       => array(
					'action'  => 'plugin_information',
					'request' => $serial_args
				)
			);

			$res_details = new \stdClass();
			$res_details->result = 'error';

			//$res_details = \ARI_RmlVersionUpgrader\RML_Version_Upgrader::HandleUpgradeInfoRequest($args);

			$rml_remote = wp_remote_post( $url_to_read_more_login_com, $http_args );
			if ( ! is_wp_error( $rml_remote ) && isset( $rml_remote['response']['code'] ) && $rml_remote['response']['code'] == 200 && ! empty( $rml_remote['body'] ) ) {
				$res_details = maybe_unserialize( wp_remote_retrieve_body( $rml_remote ) );
				if ( get_class( $res_details ) !== 'stdClass' ) {
					$res_details = new \stdClass();
					$res_details->result = 'error';
				}
			}

			$res_details->request_args = $serial_args;
			set_transient( self::ARI_READ_MORE_LOGIN_UPGRADE, $res_details, 43200 ); // 12 hours cache
		}

		if ( $res_details->result === 'success' ) {
			return $res_details;
		}
		elseif ( $res_details->result === 'none' ) {
			return false;
		} else {
			$rml_custom_upgrade_result = $res_details->result;
			$rml_custom_upgrade_message = $res_details->message;
			return false;
		}
	}

	static function version_compare2($v1, $v2, $operator) {
		$v1 = str_replace(' ', '', $v1);
		$v1 = strtolower($v1);
		$v2 = str_replace(' ', '', $v2);
		$v2 = strtolower($v2);
		return version_compare($v1, $v2, $operator);
	}

	static function InjectCustomPluginInfo( &$res, $rml_info ) {
		if ( $rml_info->edition === 'standard' ) {
			/* If WordPress directory has newer plugin, use that. */
			$plugin             = PluginContainer::Instance();
			$my_plugin_version  = $plugin->GetPluginVersion();
			if ( self::version_compare2( $my_plugin_version, $res->version, '>' ) ) {
				$current_wp_version = get_bloginfo( 'version' );
				if ( self::version_compare2( $rml_info->version, $my_plugin_version, '>' ) && self::version_compare2( $current_wp_version, $rml_info->requires, '>' ) ) {
					$res->slug        = $rml_info->slug;
					$res->plugin      = 'read-more-login/read-more-login.php';
					$res->new_version = $rml_info->version_name;
					$res->tested      = $rml_info->tested;
					$res->package     = $rml_info->download_link;
					$res->url         = $rml_info->homepage;
					$res->rml_update_message= $rml_info->update_message;
					return true;
				}
			}
		} elseif ( $rml_info->edition === 'premium' ) {
			$current_wp_version = get_bloginfo( 'version' );
			if ( self::version_compare2( $current_wp_version, $rml_info->requires, '>' ) ) {
				$res->slug        = $rml_info->slug;
				$res->plugin      = 'read-more-login/read-more-login.php';
				$res->new_version = $rml_info->version_name;
				$res->tested      = $rml_info->tested;
				$res->package     = $rml_info->download_link;
				$res->url         = $rml_info->homepage;
				$res->rml_update_message= $rml_info->update_message;
				return true;
			}
		}

		return false;
	}

	static function site_transient_update_plugins( $transient, $args ) {
		if (( defined( 'READ_MORE_LOGIN_ENTER_LICENSE_EDITION' ) and (READ_MORE_LOGIN_ENTER_LICENSE_EDITION === True))
		or ( defined( 'READ_MORE_LOGIN_DOWNLOAD_PRE_RELEASE' ) and (READ_MORE_LOGIN_DOWNLOAD_PRE_RELEASE === True))
		or ( defined( 'READ_MORE_LOGIN_DOWNLOAD_DEV_VERSION' ) and (READ_MORE_LOGIN_DOWNLOAD_DEV_VERSION === True))) {
			$options = get_option(SettingsAdvancedOptions::OPTION_NAME);
			if((isset($options[SettingsAdvancedOptions::LICENSE_KEY]) and ($options[SettingsAdvancedOptions::LICENSE_KEY]))
			or (isset($options[SettingsAdvancedOptions::DOWNLOAD_RC_VERSIONS]) and ($options[SettingsAdvancedOptions::DOWNLOAD_RC_VERSIONS]))
			or (isset($options[SettingsAdvancedOptions::DOWNLOAD_BETA_VERSIONS]) and ($options[SettingsAdvancedOptions::DOWNLOAD_BETA_VERSIONS]))
			or (isset($options[SettingsAdvancedOptions::DOWNLOAD_DEV_VERSIONS]) and ($options[SettingsAdvancedOptions::DOWNLOAD_DEV_VERSIONS]))) {
				if ($transient === false) {
					return $transient;
				}
				if ( empty( $transient->checked ) ) {
					return $transient;
				}

				$rml_info = self::retreive_rml_version_data($options);

				if ( $rml_info != false ) {
					/* Read plugin version available at WordPress plugin directory. */
					if ( isset( $transient->response['read-more-login/read-more-login.php'] ) ) {
						$version_available = $transient->response['read-more-login/read-more-login.php']->new_version;
					} elseif ( isset( $transient->no_update['read-more-login/read-more-login.php'] ) ) {
						$version_available = $transient->no_update['read-more-login/read-more-login.php']->new_version;
					} else {
						$version_available = '0';
					}

					$res = new \stdClass();
					$res->version = $version_available;
					if ( self::InjectCustomPluginInfo($res, $rml_info) ) {
						$transient->response[ $res->plugin ] = $res;
					}
				}
			}
		}

		return $transient;
	}

	static function filter_plugins_api_result($res, $action, $args) {
		if (( defined( 'READ_MORE_LOGIN_ENTER_LICENSE_EDITION' ) and (READ_MORE_LOGIN_ENTER_LICENSE_EDITION === True))
		or ( defined( 'READ_MORE_LOGIN_DOWNLOAD_PRE_RELEASE' ) and (READ_MORE_LOGIN_DOWNLOAD_PRE_RELEASE === True))
		or ( defined( 'READ_MORE_LOGIN_DOWNLOAD_DEV_VERSION' ) and (READ_MORE_LOGIN_DOWNLOAD_DEV_VERSION === True))) {
			if ( $action === 'plugin_information' ) {
				if ( $args->slug === 'read-more-login' ) {
					$options = get_option( SettingsAdvancedOptions::OPTION_NAME );
					if (
						( isset( $options[ SettingsAdvancedOptions::LICENSE_KEY ] ) and ( $options[ SettingsAdvancedOptions::LICENSE_KEY ] ) ) or ( isset( $options[ SettingsAdvancedOptions::DOWNLOAD_RC_VERSIONS ] ) and ( $options[ SettingsAdvancedOptions::DOWNLOAD_RC_VERSIONS ] ) ) or ( isset( $options[ SettingsAdvancedOptions::DOWNLOAD_BETA_VERSIONS ] ) and ( $options[ SettingsAdvancedOptions::DOWNLOAD_BETA_VERSIONS ] ) ) or ( isset( $options[ SettingsAdvancedOptions::DOWNLOAD_DEV_VERSIONS ] ) and ( $options[ SettingsAdvancedOptions::DOWNLOAD_DEV_VERSIONS ] ) )
					) {
						$rml_info = self::retreive_rml_version_data( $options );
						if ( $rml_info != false ) {
							if ( self::InjectCustomPluginInfo( $res, $rml_info ) ) {
								$res->version  = $rml_info->version_name;
								$res->sections = $rml_info->sections;
							}
						}
					}
				}
			}
		}
		return $res;
	}

	static function add_after_plugin_row($plugin_file, $plugin_data, $status ) {
		global $rml_custom_upgrade_result, $rml_custom_upgrade_message;
		if ( $rml_custom_upgrade_result === 'error'){
			printf('<tr class="plugin-update-tr active update" data-slug="read-more-login" data-plugin="read-more-login/read-more-login.php">');
			printf('<td colspan="3" class="plugin-update colspanchange">');
			printf('<div class="update-message notice inline notice-error notice-alt">');
			printf('<p>%s</p>', esc_attr($rml_custom_upgrade_message));
			printf('</div>');
			printf('</td>');
			printf('</tr>');
		}
	}

	static function add_in_plugin_update_message($plugin_data, $response ) {
		if($response->rml_update_message) {
			printf( ' <span style="font-weight: bold;color:red"> %s</span>', esc_attr( $response->rml_update_message ) );
		}
	}

	static function delete_transient() {
		delete_transient( self::ARI_READ_MORE_LOGIN_UPGRADE );
	}
}
