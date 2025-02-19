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

class AdminPreviewData extends OptionModel
{
    const OPTION_NAME = 'ari_read-more-login_admin_preview_options';

    const USERNAME ='username';
    const EMAILS = 'emails';
    const FIRST_NAME ='first_name';
    const LAST_NAME ='last_name';

    /* Metadata describing database fields and data properties: */
    static $MetaData = array
    (
        self::USERNAME => array(
            'data_type'     => 'StringType',
            'default_value' => '',
        ),
        self::EMAILS => array(
            'data_type'     => 'StringType',
            'default_value' => '',
        ),
        self::FIRST_NAME => array(
            'data_type'     => 'StringType',
            'default_value' => '',
        ),
        self::LAST_NAME => array(
            'data_type'     => 'StringType',
            'default_value' => '',
        )
    );
}
