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

use WP_PluginFramework\Utils\DebugLogger;

class PluginUpgrade
{
    static function GetPreviousVersion()
    {
        $previous_plugin_version = false;
        $option = get_option('aru_readmorelogin_textfading_option');
        if(isset($option))
        {
            if ($option !== false)
            {
                if (is_array($option))
                {
                    if (array_key_exists('fade_height', $option))
                    {
                        /* Version 1 detected. */
                        $previous_plugin_version = '1';
                    }
                }
            }
        }
        return $previous_plugin_version;
    }

    static function Upgrade($plugin_slug, $plugin_version, $current_plugin_version=false)
    {
        /* Remove additional info like beta version appended to string. */
        $plugin_version = trim($plugin_version);
	    if ( strstr( $plugin_version, '-') ) {
		    $plugin_version = strstr($plugin_version, '-', true);
	    }

        $current_plugin_version = trim($current_plugin_version);
	    if ( strstr( $current_plugin_version, '-') ) {
		    $current_plugin_version = strstr($current_plugin_version, '-', true);
	    }

        if($current_plugin_version === false)
        {
            /* Check if any options from previous version exists. */
            $current_plugin_version = self::GetPreviousVersion();
        }

        if($current_plugin_version !== false)
        {
            if ($plugin_version != $current_plugin_version)
            {
                if($current_plugin_version ==='1')
                {
                    $next_version = '2.0.0';
                    DebugLogger::WriteDebugNote( 'Step upgrade from ' . $current_plugin_version . ' to ' . $next_version);
                    if(!self::Upgrade_from_1_to_2_0())
                    {
                        DebugLogger::WriteDebugWarning( 'Upgrading ' . $plugin_slug . ' from ' . $current_plugin_version . ' to ' . $next_version . ' failed. Using default settings.' );
                    }
                    $current_plugin_version = $next_version;
                }

                if($current_plugin_version === '2.0.0')
                {
                    $next_version = '2.0.1';
                    DebugLogger::WriteDebugNote( 'Step upgrade from ' . $current_plugin_version . ' to ' . $next_version);
                    /* Noting to upgrade. */
                    $current_plugin_version = $next_version;
                }

                if($current_plugin_version === '2.0.1')
                {
                    $next_version = '2.0.2';
                    DebugLogger::WriteDebugNote( 'Step upgrade from ' . $current_plugin_version . ' to ' . $next_version);
                    /* Noting to upgrade. */
                    $current_plugin_version = $next_version;
                }

	            if($current_plugin_version === '2.0.2')
	            {
		            $next_version = '2.0.3';
		            DebugLogger::WriteDebugNote( 'Step upgrade from ' . $current_plugin_version . ' to ' . $next_version);
		            /* Noting to upgrade. */
		            $current_plugin_version = $next_version;
	            }
            }

            if($current_plugin_version !== $plugin_version)
            {
                DebugLogger::WriteDebugWarning('Unhandled upgrade of ' . $plugin_slug . ' from version ' . $current_plugin_version . ' to ' . $plugin_version . '.');
            }
        }
    }

    static function Upgrade_from_1_to_2_0()
    {
        $new_fade_enable = null;
        $new_fade_height = null;
        $new_fade_bg_col = null;
        $new_GoogleVisitorsFree = null;
        $new_EnableDebugLog = null;
        $new_EnableExtraDebugLog = null;
        $new_RegisterPageLink = null;
        $new_LoginPageLink = null;
        $new_ProfilePageLink = null;
        $new_PasswordPageLink = null;
        $new_TermsPage = null;
        $new_LogoutPage = null;
        $new_register_email_reply_addr = null;
        $new_register_email_subject = null;
        $new_register_email_body = null;
        $new_password_email_reply_addr = null;
        $new_password_email_subject = null;
        $new_password_email_body = null;
        $new_send_notification = null;
        $new_notification_email_reply_addr = null;
        $new_notification_email_sendto = null;
        $new_notification_email_subject = null;
        $new_notification_email_body = null;

        $previous_settings = get_option('aru_readmorelogin_textfading_option');
        if(($previous_settings) and is_array($previous_settings))
        {
            if(array_key_exists('fade_enable', $previous_settings))
            {
                $new_fade_enable = boolval($previous_settings['fade_enable']);
            }
            else
            {
                $new_fade_enable = false;
            }

            if(array_key_exists('fade_height', $previous_settings))
            {
                $new_fade_height = intval($previous_settings['fade_height']);
            }
            else
            {
                return false;
            }

            if(array_key_exists('fade_bg_col', $previous_settings))
            {
                $new_fade_bg_col = strval($previous_settings['fade_bg_col']);
            }
            else
            {
                return false;
            }
        }

        $previous_settings = get_option('aru_readmorelogin_searchengines_option');
        if(($previous_settings) and is_array($previous_settings))
        {
            if(array_key_exists('GoogleVisitorsFree', $previous_settings))
            {
                $new_GoogleVisitorsFree = boolval($previous_settings['GoogleVisitorsFree']);
            }
            else
            {
                $new_GoogleVisitorsFree = false;
            }
        }

        $previous_settings = get_option('aru_readmorelogin_debuglog_option');
        if(($previous_settings) and is_array($previous_settings))
        {
            if(array_key_exists('EnableDebugLog', $previous_settings))
            {
                $new_EnableDebugLog = boolval($previous_settings['EnableDebugLog']);
            }
            else
            {
                $new_EnableDebugLog = false;
            }

            if(array_key_exists('EnableExtraDebugLog', $previous_settings))
            {
                $new_EnableExtraDebugLog = boolval($previous_settings['EnableExtraDebugLog']);
            }
            else
            {
                $new_EnableExtraDebugLog = false;
            }
        }

        $previous_settings = get_option('aru_readmorelogin_linking_option');
        if(($previous_settings) and is_array($previous_settings))
        {
            if (array_key_exists('RegisterPageLink', $previous_settings))
            {
                $new_RegisterPageLink = strval($previous_settings['RegisterPageLink']);
            }
            else
            {
                return false;
            }

            if (array_key_exists('LoginPageLink', $previous_settings))
            {
                $new_LoginPageLink = strval($previous_settings['LoginPageLink']);
            }
            else
            {
                return false;
            }

            if (array_key_exists('ProfilePageLink', $previous_settings))
            {
                $new_ProfilePageLink = strval($previous_settings['ProfilePageLink']);
            }
            else
            {
                return false;
            }

            if (array_key_exists('PasswordPageLink', $previous_settings))
            {
                $new_PasswordPageLink = strval($previous_settings['PasswordPageLink']);
            }
            else
            {
                return false;
            }

            if (array_key_exists('TermsPage', $previous_settings))
            {
                $new_TermsPage = strval($previous_settings['TermsPage']);
            }
            else
            {
                return false;
            }
        }

        $previous_settings = get_option('aru_readmorelogin_redirect_option');
        if(($previous_settings) and is_array($previous_settings))
        {
            if (array_key_exists('LogoutPage', $previous_settings))
            {
                $new_LogoutPage = strval($previous_settings['LogoutPage']);
            }
            else
            {
                return false;
            }
        }

        $previous_settings = get_option('aru_readmorelogin_email_verification_option');
        if(($previous_settings) and is_array($previous_settings))
        {
            /* Note: _replay_ was spelled wrong in previous version. Dont change this. */
            if (array_key_exists('email_replay_addr', $previous_settings))
            {
                $new_register_email_reply_addr = strval($previous_settings['email_replay_addr']);
            }
            else
            {
                return false;
            }

            if (array_key_exists('email_subject', $previous_settings))
            {
                $new_register_email_subject = strval($previous_settings['email_subject']);
            }
            else
            {
                return false;
            }

            if (array_key_exists('email_body', $previous_settings))
            {
                $new_register_email_body = strval($previous_settings['email_body']);
            }
            else
            {
                return false;
            }
        }

        $previous_settings = get_option('aru_readmorelogin_email_reset_password_option');
        if(($previous_settings) and is_array($previous_settings))
        {
            /* Note: _replay_ was spelled wrong in previous version. Dont change this. */
            if (array_key_exists('email_replay_addr', $previous_settings))
            {
                $new_password_email_reply_addr = strval($previous_settings['email_replay_addr']);
            }
            else
            {
                return false;
            }

            if (array_key_exists('email_subject', $previous_settings))
            {
                $new_password_email_subject = strval($previous_settings['email_subject']);
            }
            else
            {
                return false;
            }

            if (array_key_exists('email_body', $previous_settings))
            {
                $new_password_email_body = strval($previous_settings['email_body']);
            }
            else
            {
                return false;
            }
        }

        $previous_settings = get_option('aru_readmorelogin_email_register_notification_option');
        if(($previous_settings) and is_array($previous_settings))
        {
            if (array_key_exists('send_notification', $previous_settings))
            {
                $new_send_notification = boolval($previous_settings['send_notification']);
            }
            else
            {
                $new_send_notification = false;
            }

            /* Note: _replay_ was spelled wrong in previous version. Dont change this. */
            if (array_key_exists('email_replay_addr', $previous_settings))
            {
                $new_notification_email_reply_addr = strval($previous_settings['email_replay_addr']);
            }
            else
            {
                return false;
            }

            if (array_key_exists('email_sendto', $previous_settings))
            {
                $new_notification_email_sendto = strval($previous_settings['email_sendto']);
            }
            else
            {
                return false;
            }

            if (array_key_exists('email_subject', $previous_settings))
            {
                $new_notification_email_subject = strval($previous_settings['email_subject']);
            }
            else
            {
                return false;
            }

            if (array_key_exists('email_body', $previous_settings))
            {
                $new_notification_email_body = strval($previous_settings['email_body']);
            }
            else
            {
                return false;
            }
        }

        $style_options = new SettingsStyleOptions();
        $style_options->LoadData();

        if(isset($new_fade_enable))
        {
            $style_options->SetData(SettingsStyleOptions::FADING_ENABLE, $new_fade_enable);
        }

        if(isset($new_fade_height))
        {
            $style_options->SetData(SettingsStyleOptions::FADING_HEIGHT, $new_fade_height);
        }

        if(isset($new_fade_bg_col))
        {
            $style_options->SetData(SettingsStyleOptions::FADE_BACKGROUND_COLOR, $new_fade_bg_col);
        }

        $style_options->SetData(SettingsStyleOptions::FRAME_BORDER_SIZE, 0);
        $style_options->SetData(SettingsStyleOptions::FORM_LOADING, SettingsStyleOptions::FORM_LOADING_NO_ANIMATION);
        $style_options->SetData(SettingsStyleOptions::TEXT_LOADING, SettingsStyleOptions::TEXT_LOADING_NO_ANIMATION);

        $style_options->SaveData();


        $email_options = new SettingsEmailOptions();
        $email_options->LoadData();

        if(isset($new_register_email_reply_addr))
        {
            $email_options->SetData(SettingsEmailOptions::READMORE_REGISTER_REPLY_ADDRESS, $new_register_email_reply_addr);
        }

        if(isset($new_register_email_subject))
        {
            $email_options->SetData(SettingsEmailOptions::READMORE_REGISTER_SUBJECT, $new_register_email_subject);
        }

        if(isset($new_register_email_body))
        {
            $email_options->SetData(SettingsEmailOptions::READMORE_REGISTER_BODY, $new_register_email_body);
        }

        if(isset($new_register_email_reply_addr))
        {
            $email_options->SetData(SettingsEmailOptions::REGISTER_REPLAY_ADDRESS, $new_register_email_reply_addr);
        }

        if(isset($new_register_email_subject))
        {
            $email_options->SetData(SettingsEmailOptions::REGISTER_SUBJECT, $new_register_email_subject);
        }

        if(isset($new_register_email_body))
        {
            $email_options->SetData(SettingsEmailOptions::REGISTER_BODY, $new_register_email_body);
        }

        if(isset($new_password_email_reply_addr))
        {
            $email_options->SetData(SettingsEmailOptions::PASSWORD_REPLAY_ADDRESS, $new_password_email_reply_addr);
        }

        if(isset($new_password_email_subject))
        {
            $email_options->SetData(SettingsEmailOptions::PASSWORD_SUBJECT, $new_password_email_subject);
        }

        if(isset($new_password_email_body))
        {
            $email_options->SetData(SettingsEmailOptions::PASSWORD_BODY, $new_password_email_body);
        }

        if(isset($new_send_notification))
        {
            $email_options->SetData(SettingsEmailOptions::NOTIFICATION_ENABLE, $new_send_notification);
        }

        if(isset($new_notification_email_sendto))
        {
            $email_options->SetData(SettingsEmailOptions::NOTIFICATION_SEND_TO, $new_notification_email_sendto);
        }

        if(isset($new_notification_email_reply_addr))
        {
            $email_options->SetData(SettingsEmailOptions::NOTIFICATION_REPLAY_ADDRESS, $new_notification_email_reply_addr);
        }

        if(isset($new_notification_email_subject))
        {
            $email_options->SetData(SettingsEmailOptions::NOTIFICATION_SUBJECT, $new_notification_email_subject);
        }

        if(isset($new_notification_email_body))
        {
            $email_options->SetData(SettingsEmailOptions::NOTIFICATION_BODY, $new_notification_email_body);
        }

        $email_options->SaveData();


        $linking_options = new SettingsLinkingOptions();
        $linking_options->LoadData();

        if(isset($new_RegisterPageLink))
        {
            $linking_options->SetData(SettingsLinkingOptions::REGISTER_PAGE_LINK, $new_RegisterPageLink);
        }

        if(isset($new_LoginPageLink))
        {
            $linking_options->SetData(SettingsLinkingOptions::LOGIN_PAGE_LINK, $new_LoginPageLink);
        }

        if(isset($new_ProfilePageLink))
        {
            $linking_options->SetData(SettingsLinkingOptions::PROFILE_PAGE_LINK, $new_ProfilePageLink);
        }

        if(isset($new_PasswordPageLink))
        {
            $linking_options->SetData(SettingsLinkingOptions::PASSWORD_PAGE_LINK, $new_PasswordPageLink);
        }

        if(isset($new_TermsPage))
        {
            $linking_options->SetData(SettingsLinkingOptions::TERMS_PAGE, $new_TermsPage);
        }

        if(isset($new_LogoutPage))
        {
            $linking_options->SetData(SettingsLinkingOptions::LOGOUT_PAGE_REDIRECT, $new_LogoutPage);
        }

        $linking_options->SaveData();


        $access_options = new SettingsAccessOptions();
        $access_options->LoadData();

        if(isset($new_GoogleVisitorsFree))
        {
            $access_options->SetData(SettingsAccessOptions::GOOGLE_READ, $new_GoogleVisitorsFree);
        }

        $access_options->SaveData();

        $advanced_options = new SettingsAdvancedOptions();
        $advanced_options->LoadData();

        if(isset($new_EnableDebugLog))
        {
            $advanced_options->SetData(SettingsAdvancedOptions::ENABLE_DEBUG_LOG, $new_EnableDebugLog);
        }

        if(isset($new_EnableExtraDebugLog))
        {
            $advanced_options->SetData(SettingsAdvancedOptions::ENABLE_EXTRA_DEBUG_LOGGING, $new_EnableExtraDebugLog);
        }

        $advanced_options->SaveData();


        return true;
    }
}
