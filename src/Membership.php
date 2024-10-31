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

class Membership
{
    static public function RegistrationPage()
    {
        if (is_user_logged_in())
        {
            $controller = new LoginController();
        }
        else
        {
            $controller = new RegistrationController();
        }

        return $controller->Draw();
    }

    static public function MembershipLogin()
    {
        $controller = new LoginController();

        return $controller->Draw();

    }

    static public function ProfilePage()
    {
        $html = '';
        if (is_user_logged_in())
        {
            $controller = new ProfileController();
        }
        else
        {
            $html .= '<p>' . esc_html__('You must log in to access your user profile.', 'read-more-login') . '</p>';
            $controller = new LoginController();
        }

        $html .= $controller->Draw();
        return $html;
    }

    static public function PasswordPage()
    {
        if (is_user_logged_in())
        {
            $controller = new PasswordChangeController();
        }
        else
        {
            $controller = new PasswordRecoveryController();
        }

        return $controller->Draw();
    }

    static public function ScheduleEvent()
    {
        DebugLogger::WriteDebugNote('ScheduleEvent.');

        $clear_timeout = 30 * 24 * 60 * 60;  // Timeout for when the record will be removed from database
        $register_timeout = 2 * 24 * 60 * 60;  // Timeout for valid registration link
        $password_timeout = 60 * 60;  // Timeout for valid password link

        $reg_data = new RegistrationDbTable();
        $count = $reg_data->LoadAllData();

        while ($count > 0)
        {
            $timestamp_obj = $reg_data->GetDataObject(RegistrationDbTable::TIMESTAMP);
            $timestamp = $timestamp_obj->GetValueSeconds();

            $now = time();

            if (($timestamp + $clear_timeout) < $now)
            {
                $reg_data->Delete();
            }
            else
            {
                $state = $reg_data->GetDataIndex(0, RegistrationDbTable::STATE);
                $deleted = false;

                switch ($state)
                {
                    case RegistrationDbTable::STATE_REGISTRATION_STARTED:
                    case RegistrationDbTable::STATE_REGISTRATION_EMAIL_SENT:
                    case RegistrationDbTable::STATE_REGISTRATION_EMAIL_CONFIRMED:
                    case RegistrationDbTable::STATE_REGISTRATION_MORE_INFO:
                        if (($timestamp + $register_timeout) < $now)
                        {
                            $reg_data->SetData(RegistrationDbTable::STATE, RegistrationDbTable::STATE_REGISTRATION_EXPIRED);
                            $reg_data->SaveData();
                            $id = $reg_data->GetData(RegistrationDbTable::PRIMARY_KEY);
                            DebugLogger::WriteDebugNote('Expired registration data record for id=', $id);
                        }
                        break;

                    case RegistrationDbTable::STATE_RESET_PASSWORD_EMAIL_SENT:
                    case RegistrationDbTable::STATE_RESET_PASSWORD_EMAIL_CONFIRM:
                        if (($timestamp + $password_timeout) < $now)
                        {
                            $reg_data->SetData(RegistrationDbTable::STATE, RegistrationDbTable::STATE_RESET_PASSWORD_EXPIRED);
                            $reg_data->SaveData();
                            $id = $reg_data->GetData(RegistrationDbTable::PRIMARY_KEY);
                            DebugLogger::WriteDebugNote('Expired password registration data record for id=', $id);
                        }
                        break;

                    case RegistrationDbTable::STATE_REGISTRATION_USER_CREATED:
                    case RegistrationDbTable::STATE_RESET_PASSWORD_DONE:
                    case RegistrationDbTable::STATE_RESET_PASSWORD_EXPIRED:
                    case RegistrationDbTable::STATE_REGISTRATION_EXPIRED:
                    case RegistrationDbTable::STATE_REGISTRATION_NOT_SET:
                        break;

                    default:
                        $reg_id = $reg_data->GetData(RegistrationDbTable::PRIMARY_KEY);
                        DebugLogger::WriteDebugError('Missing state handler for ' . $state . '. Delete reg_id ' . $reg_id);
                        $reg_data->Delete();
                        $deleted = true;
                        break;

                }

                if (!$deleted)
                {
                    $reg_data->FetchData();
                }
            }
            $count--;
        }
    }

    static public function GetRedirectLogout()
    {
        $linking_options = new SettingsLinkingOptions();
        $url = $linking_options->GetCompleteLinkUrl(SettingsLinkingOptions::LOGOUT_PAGE_REDIRECT, true);
        return $url;
    }

    static public function RedirectLogout($url)
    {
        $linking_options = new SettingsLinkingOptions();
        $set_url = $linking_options->GetCompleteLinkUrl(SettingsLinkingOptions::LOGOUT_PAGE_REDIRECT, true);

        if($set_url !== false)
        {
            $url = $set_url;
        }
        else
        {
            $url = $_SERVER['HTTP_REFERER'];
        }

        return $url;
    }

    static public function RedirectRegister($url)
    {
        $linking_options = new SettingsLinkingOptions();
        $set_url = $linking_options->GetCompleteLinkUrl(SettingsLinkingOptions::REGISTER_PAGE_LINK, true);

        if($set_url !== false)
        {
            $url = $set_url;
        }

        return $url;
    }

    static public function RedirectLogin($url)
    {
        $linking_options = new SettingsLinkingOptions();
        $set_url = $linking_options->GetCompleteLinkUrl(SettingsLinkingOptions::LOGIN_PAGE_LINK, true);

        if($set_url !== false)
        {
            $url = $set_url;
        }

        return $url;
    }

    static public function RedirectLostPassword($url)
    {
        $linking_options = new SettingsLinkingOptions();
        $set_url = $linking_options->GetCompleteLinkUrl(SettingsLinkingOptions::PASSWORD_PAGE_LINK, true);

        if($set_url !== false)
        {
            $url = $set_url;
        }

        return $url;
    }
}
