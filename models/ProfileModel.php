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

use WP_PluginFramework\Models\Model;

class ProfileModel extends Model
{
    const USER_LOGIN = 'user_login';
    const FIRST_NAME = 'first_name';
    const LAST_NAME = 'last_name';
    const USER_EMAIL = 'user_email';

    /* Metadata describing database fields and data properties: */
    static $MetaData = array
    (
        self::USER_LOGIN    => array(
            'data_type'     => 'StringType',
            'default_value' => '',
            'readonly'      => true
        ),
        self::FIRST_NAME    => array(
            'data_type'     => 'StringType',
            'default_value' => ''
        ),
        self::LAST_NAME     => array(
            'data_type'     => 'StringType',
            'default_value' => ''
        ),
        self::USER_EMAIL    => array(
            'data_type'     => 'EmailType',
            'default_value' => '',
            'required'      => true
        )
    );

    public function LoadDataRecord($field_name_list)
    {
        if(is_user_logged_in())
        {
            $current_user = wp_get_current_user();

            $user_data = array(
                self::USER_LOGIN => $current_user->user_login,
                self::FIRST_NAME => $current_user->first_name,
                self::LAST_NAME => $current_user->last_name,
                self::USER_EMAIL => $current_user->user_email
            );

            $this->AddDataRecord($user_data);

            return 1;
        }

        return false;
    }

    public function LoadColumn($field_name_list)
    {
    }

    public function SaveDataIndex($index)
    {
        if (is_user_logged_in())
        {
            $data_record = $this->GetDataRecord();

            $user_data = wp_get_current_user();

            $user_data->first_name = addslashes($data_record[self::FIRST_NAME]);
            $user_data->last_name = addslashes($data_record[self::LAST_NAME]);
            $user_data->user_email = addslashes($data_record[self::USER_EMAIL]);

            $user_id = wp_update_user($user_data);

            if (is_wp_error($user_id))
            {
                return false;
            }
            else
            {
                return true;
            }
        }

        return null;
    }

    public function IsRequiredField($key)
    {
        $register_options = get_option(SettingsFormOptions::OPTION_NAME);

        switch($key)
        {
            case static::FIRST_NAME:
                return $register_options[SettingsFormOptions::REGISTER_FIRST_NAME_REQUIRED];
                break;

            case static::LAST_NAME:
                return $register_options[SettingsFormOptions::REGISTER_LAST_NAME_REQUIRED];
                break;

            default:
                return parent::IsRequiredField($key);
                break;
        }
    }
}
