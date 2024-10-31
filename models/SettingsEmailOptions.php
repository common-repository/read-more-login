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

class SettingsEmailOptions extends OptionModel
{
    const OPTION_NAME = 'ari_read-more-login_email_options';

    //const READMORE_VERIFY_EMAILS = 'readmore_verify_emails';
    const READMORE_REGISTER_REPLY_ADDRESS = 'readmore_register_reply_address';
    const READMORE_REGISTER_SUBJECT = 'readmore_register_subject';
    const READMORE_REGISTER_BODY = 'readmore_register_body';

    const REGISTER_VERIFY_EMAILS = 'register_verify_emails';
    const REGISTER_REPLAY_ADDRESS = 'register_reply_address';
    const REGISTER_SUBJECT = 'register_subject';
    const REGISTER_BODY = 'register_body';

    const PASSWORD_REPLAY_ADDRESS = 'password_reply_address';
    const PASSWORD_SUBJECT = 'password_subject';
    const PASSWORD_BODY = 'password_body';

    const NOTIFICATION_ENABLE = 'notification_enable';
    const NOTIFICATION_SEND_TO = 'notification_receiver';
    const NOTIFICATION_REPLAY_ADDRESS = 'notification_reply_address';
    const NOTIFICATION_SUBJECT = 'notification_subject';
    const NOTIFICATION_BODY = 'notification_body';


    /* Metadata describing database fields and data properties: */
    static $MetaData = array
    (
        /*
        self::READMORE_VERIFY_EMAILS => array(
            'data_type'     => 'BooleanType',
            'default_value' => '1',
        ),
        */
        self::READMORE_REGISTER_REPLY_ADDRESS => array(
            'data_type'     => 'StringType',
            'default_value' => ''
        ),
        self::READMORE_REGISTER_SUBJECT => array(
            'data_type'     => 'StringType',
            'default_value' => ''
        ),
        self::READMORE_REGISTER_BODY => array(
            'data_type'     => 'StringType',
            'default_value' => ''
        ),
        /*
        self::REGISTER_VERIFY_EMAILS => array(
            'data_type'     => 'BooleanType',
            'default_value' => '1',
        ),
        */
        self::REGISTER_REPLAY_ADDRESS => array(
            'data_type'     => 'StringType',
            'default_value' => ''
        ),
        self::REGISTER_SUBJECT => array(
            'data_type'     => 'StringType',
            'default_value' => ''
        ),
        self::REGISTER_BODY => array(
            'data_type'     => 'StringType',
            'default_value' => ''
        ),
        self::PASSWORD_REPLAY_ADDRESS => array(
            'data_type'     => 'StringType',
            'default_value' => ''
        ),
        self::PASSWORD_SUBJECT => array(
            'data_type'     => 'StringType',
            'default_value' => ''
        ),
        self::PASSWORD_BODY => array(
            'data_type'     => 'StringType',
            'default_value' => ''
        ),
        self::NOTIFICATION_ENABLE => array(
            'data_type'     => 'BooleanType',
            'default_value' => '0'
        ),
        self::NOTIFICATION_SEND_TO => array(
            'data_type'     => 'StringType',
            'default_value' => ''
        ),
        self::NOTIFICATION_REPLAY_ADDRESS => array(
            'data_type'     => 'StringType',
            'default_value' => ''
        ),
        self::NOTIFICATION_SUBJECT => array(
            'data_type'     => 'StringType',
            'default_value' => ''
        ),
        self::NOTIFICATION_BODY => array(
            'data_type'     => 'StringType',
            'default_value' => ''
        )
    );

    public function GetMetaDataList()
    {
        $metadata =  static::$MetaData;

        /* translators: Text sent in registration e-mail, subject line. %s: Post/page tittle. */
        $metadata[self::READMORE_REGISTER_SUBJECT]['default_value'] = sprintf(esc_html__('Register to read %s', 'read-more-login'), '%title%');
        /* translators: Text sent in registration e-mail, message body. %s: User's name. */
        $metadata[self::READMORE_REGISTER_BODY]['default_value'] = '<p>' . sprintf(esc_html__('Hello %s!', 'read-more-login'), '<strong>%name%</strong>') . '</p>&#13;&#10;';
        /* translators: Text sent in registration e-mail, message body. %1$s is name of article. %2$s is name of the site. */
        $metadata[self::READMORE_REGISTER_BODY]['default_value'] .= '<p>' . sprintf(esc_html__('Thank you for your interest in %1$s at %2$s.', 'read-more-login'), '<strong>%title%</strong>','<strong>%site_name%</strong>') . '</p>&#13;&#10;';
        /* translators: Text sent in registration e-mail, message body. */
        $metadata[self::READMORE_REGISTER_BODY]['default_value'] .= '<p>' . esc_html__('To read the remaining article follow this link.', 'read-more-login') . '</p>&#13;&#10;';
        /* translators: Text sent in registration e-mail, message body. */
        $metadata[self::READMORE_REGISTER_BODY]['default_value'] .= '<p>' . esc_html__('Please click or copy the link into your web browser:', 'read-more-login') . '</p>&#13;&#10;<p>%link%</p>';

        /* translators: Text sent in registration e-mail, subject line. %s: Name of site. */
        $metadata[self::REGISTER_SUBJECT]['default_value'] = sprintf(esc_html__('Registration at %s', 'read-more-login'), '%site_name%');
        /* translators: Text sent in registration e-mail, message body. %s: Name of site. */
        $metadata[self::REGISTER_BODY]['default_value'] = '<p>' . sprintf(esc_html__('Follow this link to complete your registration at %s.', 'read-more-login'), '<strong>%site_name%</strong>') . '</p>&#13;&#10;';
        /* translators: Text sent in registration e-mail, message body. */
        $metadata[self::REGISTER_BODY]['default_value'] .= '<p>' . esc_html__('Click or copy the link into your web browser:', 'read-more-login') . '</p>&#13;&#10;<p>%link%</p>';

        /* translators: Text sent in password reset e-mail, subject line. */
        $metadata[self::PASSWORD_SUBJECT]['default_value'] = esc_html__('Recover username and reset password', 'read-more-login');
        /* translators: Text sent in password reset e-mail, message body. %s: Name of site. */
        $metadata[self::PASSWORD_BODY]['default_value'] .= '<p>' . sprintf(esc_html__('You have requested to recover your username or reset your password at %s.', 'read-more-login'), '<strong>%site_name%</strong>') . '</p>&#13;&#10;';
        /* translators: Text sent in password reset e-mail, message body. %s: Username. */
        $metadata[self::PASSWORD_BODY]['default_value'] .= '<p>' . sprintf(esc_html__('Your username is %s', 'read-more-login'), '<strong>%username%</strong>') . '</p>&#13;&#10;';
        /* translators: Text sent in password reset e-mail, message body. */
        $metadata[self::PASSWORD_BODY]['default_value'] .= '<p>' . esc_html__('Use this link to reset your password:','read-more-login') . '</p>&#13;&#10;';
        $metadata[self::PASSWORD_BODY]['default_value'] .= '<p>%link%</p>&#13;&#10;';
        /* translators: Text sent in password reset e-mail, message body. */
        $metadata[self::PASSWORD_BODY]['default_value'] .= '<p>' . esc_html__('Please note the link can only be used once and will expire after 24 hours.', 'read-more-login') . '</p>';

        /* translators: Text sent in admin notification e-mail, subject line. */
        $metadata[self::NOTIFICATION_SUBJECT]['default_value'] = esc_html__('Notification of new user registration', 'read-more-login');
        /* translators: Text sent in admin notification e-mail, message body. %s: Name of site. */
        $metadata[self::NOTIFICATION_BODY]['default_value'] = '<p>' . sprintf(esc_html__('A new member has registered at %s.', 'read-more-login'), '<strong>%site_name%</strong>') . '</p>&#13;&#10;';
        /* translators: Text sent in admin notification e-mail, message body. %s: Username. */
        $metadata[self::NOTIFICATION_BODY]['default_value'] .= '<p>' . sprintf(esc_html__('Username: %s','read-more-login'), '%username%') . '</p>';

        return $metadata;
    }
}
