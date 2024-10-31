<?php
/** Wordpress Read-More-Login plugin.
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

/*
Plugin Name: Read More Login
Plugin URI: https://www.readmorelogin.com
Description: Puts a combined read-more, log-in and registration form inside in posts and pages. Visitors must log in or register to read more. Remaining text will be protected and hidden for other visitors.
Version: 2.0.3
Author: Arild Hegvik
License: GNU GPLv3
License URI: license.txt
Text Domain: read-more-login
Domain Path: /languages/
*/

namespace ARU_ReadMoreLogin
{
    defined('ABSPATH') || exit;

    if (!defined('ARU_READMORELOGIN_PLUGIN_FILE'))
    {
        define('ARU_READMORELOGIN_PLUGIN_FILE', __FILE__);
    }

    if (!class_exists('ReadMoreLoginPlugin'))
    {
        include_once __DIR__ . '/src/ReadMoreLoginPlugin.php';
    }

    ReadMoreLoginPlugin::Instance();
}

namespace
{
    function rml_compact_login_widget()
    {
        return ARU_ReadMoreLogin\CompactWidget::DrawCompactWidget();
    }
}
