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

use WP_PluginFramework\Models\ActiveRecord;;

class RegistrationDbTable extends ActiveRecord
{
    /* Database table name: */
    const TABLE_NAME = 'ari_read-more-login_registration';

    /* List of table field names: */
    const TIMESTAMP = 'timestamp';
    const REG_TYPE = 'reg_type';
    const STATE = 'state';
    const USERNAME = 'username';
    const PASSWORD = 'password';
    const EMAIL = 'email';
    const WP_USER_ID = 'wp_user';
    const RETRY_COUNTER = 'retries';
    const COOKIE = 'cookie';
    const NONCE = 'nonce';
    const POST_ID = 'post_id';
    const SECRET = 'secret';
    const FIRST_NAME = 'first_name';
    const LAST_NAME = 'last_name';

    /* Registration types: */
    const REG_TYPE_NOT_SET = 0;
    const REG_TYPE_READ_MORE_REGISTRATION = 1;
    const REG_TYPE_USER_REGISTRATION = 2;
    const REG_TYPE_PASSWORD_RECOVERY = 3;
    const REG_TYPE_LOGIN = 4;
    const REG_TYPE_LOGOUT = 5;
    const REG_TYPE_PROFILE = 6;

    /* State values: */
    const STATE_REGISTRATION_STARTED = 0;
    const STATE_REGISTRATION_EMAIL_SENT = 1;
    const STATE_REGISTRATION_EMAIL_CONFIRMED = 2;
    const STATE_REGISTRATION_MORE_INFO = 3;
    const STATE_REGISTRATION_USER_CREATED = 4;
    const STATE_RESET_PASSWORD_EMAIL_SENT = 5;
    const STATE_RESET_PASSWORD_EMAIL_CONFIRM = 6;
    const STATE_RESET_PASSWORD_DONE = 7;
    const STATE_RESET_PASSWORD_EXPIRED = 8;
    const STATE_REGISTRATION_EXPIRED = 9;
    const STATE_REGISTRATION_NOT_SET = 10;

    /* Metadata describing database fields and data properties: */
    static $MetaData = array
    (
        self::TIMESTAMP     => array(
            'data_type'     => 'TimeStampType',
            'default_value' => 0
        ),
        self::REG_TYPE      => array(
            'data_type'     => 'UnsignedIntegerType',
            'default_value' => self::REG_TYPE_NOT_SET
        ),
        self::STATE         => array(
            'data_type'     => 'UnsignedIntegerType',
            'default_value' => self::STATE_REGISTRATION_NOT_SET
        ),
        self::USERNAME      => array(
            'data_type'     => 'StringType',
            'default_value' => ''
        ),
        self::PASSWORD      => array(
            'data_type'     => 'StringType',
            'default_value' => ''
        ),
        self::EMAIL         => array(
            'data_type'     => 'StringType',
            'default_value' => ''
        ),
        self::WP_USER_ID    => array(
            'data_type'     => 'UnsignedIntegerType',
            'default_value' => 0
        ),
        self::RETRY_COUNTER => array(
            'data_type'     => 'UnsignedIntegerType',
            'default_value' => 0
        ),
        self::COOKIE        => array(
            'data_type'     => 'StringType',
            'default_value' => null
        ),
        self::NONCE         => array(
            'data_type'     => 'StringType',
            'default_value' => ''
        ),
        self::POST_ID       => array(
            'data_type'     => 'UnsignedIntegerType',
            'default_value' => null
        ),
        self::SECRET        => array(
            'data_type'     => 'StringType',
            'default_value' => ''
        ),
        self::FIRST_NAME         => array(
            'data_type'     => 'StringType',
            'data_size'     => 256,
            'default_value' => ''
        ),
        self::LAST_NAME     => array(
            'data_type'     => 'StringType',
            'data_size'     => 256,
            'default_value' => ''
        ),
    );
}
