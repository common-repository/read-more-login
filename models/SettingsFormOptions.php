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

class SettingsFormOptions extends OptionModel
{
    const OPTION_NAME = 'ari_read-more-login_form_options';

    const LOGIN_HEADER_TEXT = 'login_header_text';
    const LOGIN_MESSAGE_TEXT = 'login_message_text';
    const LOGIN_SHOW_LOGIN_FORM = 'login_show_login_form';
    const LOGIN_SHOW_REGISTER_BTN = 'login_show_register_btn';
    const LOGIN_SHOW_REGISTER_AS_LINK = 'login_show_register_as_link';
    const LOGIN_SHOW_FORGOTTEN_LINK = 'login_show_forgotten_link';

    const REGISTER_HEADER_TEXT = 'register_header_text';
    const REGISTER_MESSAGE_TEXT = 'register_message_text';
    const REGISTER_COLLECT_FIRST_NAME = 'register_collect_first_name';
    const REGISTER_COLLECT_LAST_NAME = 'register_collect_last_name';
    const REGISTER_FIRST_NAME_REQUIRED = 'register_first_name_required';
    const REGISTER_LAST_NAME_REQUIRED = 'register_last_name_required';
    const REGISTER_MUST_ACCEPT_TERMS = 'register_must_accept_terms';

    const VERIFY_EMAILS = 'verify_emails';
    const CHECK_EMAIL_HEADER_TEXT = 'check_email_header_text';
    const CHECK_EMAIL_MESSAGE_TEXT = 'check_email_message_text';

    /* Metadata describing database fields and data properties: */
    static $MetaData = array
    (
        self::LOGIN_HEADER_TEXT => array(
            'data_type'     => 'StringType',
            'default_value' => ''
        ),
        self::LOGIN_MESSAGE_TEXT => array(
            'data_type'     => 'StringType',
            'default_value' => ''
        ),
        self::LOGIN_SHOW_LOGIN_FORM => array(
            'data_type'     => 'BooleanType',
            'default_value' => '1'
        ),
        self::LOGIN_SHOW_REGISTER_BTN => array(
            'data_type'     => 'BooleanType',
            'default_value' => '1'
        ),
        self::LOGIN_SHOW_REGISTER_AS_LINK => array(
            'data_type'     => 'BooleanType',
            'default_value' => '0'
        ),
        self::LOGIN_SHOW_FORGOTTEN_LINK => array(
            'data_type'     => 'BooleanType',
            'default_value' => '1'
        ),
        self::REGISTER_HEADER_TEXT => array(
            'data_type'     => 'StringType',
            'default_value' => ''
        ),
        self::REGISTER_MESSAGE_TEXT => array(
            'data_type'     => 'StringType',
            'default_value' => ''
        ),
        self::REGISTER_COLLECT_FIRST_NAME => array(
            'data_type'     =>  'BooleanType',
            'default_value' => '0'
        ),
        self::REGISTER_FIRST_NAME_REQUIRED => array(
            'data_type'     =>  'BooleanType',
            'default_value' => '0'
        ),
        self::REGISTER_COLLECT_LAST_NAME => array(
            'data_type'     =>  'BooleanType',
            'default_value' => '0'
        ),
        self::REGISTER_LAST_NAME_REQUIRED => array(
            'data_type'     =>  'BooleanType',
            'default_value' => '0'
        ),
        self::REGISTER_MUST_ACCEPT_TERMS => array(
            'data_type'     =>  'BooleanType',
            'default_value' => '1'
        ),
        self::VERIFY_EMAILS => array(
            'data_type'     => 'BooleanType',
            'default_value' => '1',
        ),
        self::CHECK_EMAIL_HEADER_TEXT => array(
            'data_type'     => 'StringType',
            'default_value' => ''
        ),
        self::CHECK_EMAIL_MESSAGE_TEXT => array(
            'data_type'     => 'StringType',
            'default_value' => ''
        )
    );

    public function GetMetaDataList()
    {
        $metadata = static::$MetaData;

        /* translators: Headline text. */
        $metadata[self::LOGIN_HEADER_TEXT]['default_value'] = esc_html__('Read more', 'read-more-login');
        $metadata[self::LOGIN_MESSAGE_TEXT]['default_value'] = esc_html__('You must log in to read the rest of this article. Please log in or register as a user.', 'read-more-login');

        $metadata[self::REGISTER_HEADER_TEXT]['default_value'] = esc_html__('Register to read more', 'read-more-login');
        $metadata[self::REGISTER_MESSAGE_TEXT]['default_value'] = esc_html__('Enter your e-mail address to register.', 'read-more-login');

        $metadata[self::CHECK_EMAIL_HEADER_TEXT]['default_value'] = esc_html__('Check your e-mail', 'read-more-login');

        /* translators: %s: E-mail address. */
        $metadata[self::CHECK_EMAIL_MESSAGE_TEXT]['default_value'] = sprintf(esc_html__('An e-mail has been sent to %s.', 'read-more-login'), '<b>%email%</b>');

        /* translators: %s: Title of article to read. */
        $metadata[self::CHECK_EMAIL_MESSAGE_TEXT]['default_value'] .= '<br><br>' . sprintf(esc_html__('Please check your e-mail. You will receive a message with a link to read the remaining article %s.', 'read-more-login'), '<b>%title%</b>');

        return $metadata;
    }
}
