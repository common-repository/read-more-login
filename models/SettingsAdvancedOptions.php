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

use WP_PluginFramework\Models\OptionModel;

class SettingsAdvancedOptions extends OptionModel
{
    const OPTION_NAME = 'ari_read-more-login_advanced_options';

    const UNINSTALL_KEEP_DB_DATA = 'uninstall_keep_db_data';
    const ENABLE_DEBUG_LOG = 'enable_debug_log';
    const ENABLE_EXTRA_DEBUG_LOGGING = 'enable_extra_debug_logging';
	const LICENSE_KEY = 'license_key';
	const DOWNLOAD_RC_VERSIONS = 'download_rc_versions';
	const DOWNLOAD_BETA_VERSIONS = 'download_beta_versions';
	const DOWNLOAD_DEV_VERSIONS = 'download_dev_versions';

    /* Metadata describing database fields and data properties: */
    static $MetaData = array
    (
        self::UNINSTALL_KEEP_DB_DATA => array(
            'data_type'     =>  'BooleanType',
            'default_value' => '0'
        ),
        self::ENABLE_DEBUG_LOG => array(
            'data_type'     =>  'BooleanType',
            'default_value' => '0'
        ),
        self::ENABLE_EXTRA_DEBUG_LOGGING => array(
            'data_type'     =>  'BooleanType',
            'default_value' => '0'
        ),
        self::LICENSE_KEY => array(
	        'data_type'     => 'StringType',
	        'default_value' => ''
        ),
        self::DOWNLOAD_RC_VERSIONS => array(
            'data_type'     =>  'BooleanType',
            'default_value' => '0'
        ),
        self::DOWNLOAD_BETA_VERSIONS => array(
            'data_type'     =>  'BooleanType',
            'default_value' => '0'
        ),
        self::DOWNLOAD_DEV_VERSIONS => array(
            'data_type'     =>  'BooleanType',
            'default_value' => '0'
        )
    );
}
