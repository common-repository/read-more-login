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

use WP_PluginFramework\HtmlComponents\StatusBar;
use WP_PluginFramework\Utils\SecurityFilter;
use WP_PluginFramework\Utils\DebugLogger;

class LoginController extends MembershipController
{
    /** @var LoginView | LogoutView */
    public $View;

    public function __construct()
    {
        $secret = null;
        $secret_required=false;

        $event = SecurityFilter::SafeReadPostRequest(self::REG_EVENT, SecurityFilter::STRING_KEY_NAME);
        $nonce = SecurityFilter::SafeReadPostRequest(self::REG_NONCE, SecurityFilter::ALPHA_NUM);
        $event_type = SecurityFilter::SafeReadPostRequest('_event_type', SecurityFilter::STRING_KEY_NAME);

        $reg_id = null;
        $reg_type = RegistrationDbTable::REG_TYPE_LOGIN;

        if($event_type === 'click')
        {
            $post_id = SecurityFilter::SafeReadPostRequest(self::REG_POST_ID, SecurityFilter::POSITIVE_INTEGER);
        }
        else
        {
            $post_id = get_the_ID();
        }

        $key = 'aru_read_more_login_user_created';
        if(isset($GLOBALS[$key]))
        {
            /* If user has just been created, and we reloaded controller to log in, must recreate the nonce as the one
            created by the registration controller is not valid for this controller. */
            $nonce = $this->CreateNonce($reg_type, $post_id);
        }

        $this->SetInitCallback('InitLogin');

        if(is_user_logged_in())
        {
            parent::__construct('ARU_ReadMoreLogin\LogoutView', $event, $reg_id, $reg_type, $post_id, $nonce, $secret, $secret_required);
        }
        else
        {
            parent::__construct('ARU_ReadMoreLogin\LoginView', $event, $reg_id, $reg_type, $post_id, $nonce, $secret, $secret_required);
        }

        $this->RegisterEvent('InitLogin', 'init', 'post');
    }

    public function InitLogin_init($event)
    {
        if(is_user_logged_in())
        {
            if($event === 'ButtonLogout_click')
            {
                $this->LogOutUser();
                $logout_url = Membership::GetRedirectLogout();
                if($logout_url !== false)
                {
                    wp_redirect($logout_url);
                    exit();
                }
            }
        }
        else
        {
            if($event === 'ButtonLogin_click')
            {
                switch ($this->GetState())
                {
                    case RegistrationDbTable::STATE_REGISTRATION_NOT_SET:
                        $username = $this->View->Username->GetText();
                        $password = $this->View->Password->GetText();
                        $remember = $this->View->Remember->GetValue();

                        if ($this->CheckLoginData($username, $password))
                        {
                            if ($this->LogInUser($username, $password, $remember))
                            {
                                $this->ResponseSetLoggedIn();
                            }
                            else
                            {
                                $this->ResponseSetError(esc_html__('Error. Wrong username or password.', 'read-more-login'));
                            }
                        }
                        break;

                    default:
                        DebugLogger::WriteDebugError('Unhandled state ' . $this->GetState());
                }
            }
        }
    }

    public function ButtonLogin_click()
    {
        $this->HideInputErrorIndications();

        if(is_user_logged_in())
        {
            switch ($this->GetState())
            {
                case RegistrationDbTable::STATE_REGISTRATION_NOT_SET:
                    $this->ReloadView('ARU_ReadMoreLogin\LogoutView');
                    $message = $this->GetServerContextData('response_message');
                    $this->View->StatusBarFooter->SetStatusText($message, StatusBar::STATUS_SUCCESS);
                    break;

                default:
                    DebugLogger::WriteDebugError('Unhandled state ' . $this->GetState());
            }
        }
        else
        {
            $message = $this->GetServerContextData('response_message');
            if(!isset($message))
            {
                /* Should really have a message here, write to debug log. */
                DebugLogger::WriteDebugError('Could not log in and no error message to user.');
            }
            $this->View->StatusBarFooter->SetStatusText($message, StatusBar::STATUS_ERROR);
        }

        $this->ShowInputErrorIndications();
    }

    public function ButtonLogout_click()
    {
        if(!is_user_logged_in())
        {
            switch ($this->GetState())
            {
                case RegistrationDbTable::STATE_REGISTRATION_NOT_SET:
                    $this->ReloadView('ARU_ReadMoreLogin\LoginView');
                    $message = $this->GetServerContextData('response_message');
                    $this->View->StatusBarFooter->SetStatusText($message, StatusBar::STATUS_SUCCESS);
                    break;

                default:
                    DebugLogger::WriteDebugError('Unhandled state ' . $this->GetState());
            }
        }
    }

    protected function DrawView($parameters=null)
    {
        $rml  = SecurityFilter::SafeReadGetRequest('rml_registering', SecurityFilter::ALPHA_NUM);
        if($rml == 'completed')
        {
            $parameters['status_message'] = sprintf(esc_html__('Registration completed. You can now log in.', 'read-more-login'));
        }
        else if($rml == 'completed2')
        {
            $parameters['status_message'] = esc_html__('User already created. You can now log in.', 'read-more-login');
        }
        else if($rml == 'emailexist')
        {
            $email  = '<strong>' . SecurityFilter::SafeReadGetRequest('rml_email', SecurityFilter::EMAIL) . '</strong>';
            $parameters['status_message'] = sprintf(esc_html__('User account with e-mail %s already exists. You can now log in.', 'read-more-login'), $email);
        }

        $rml = SecurityFilter::SafeReadGetRequest('rml_password', SecurityFilter::ALPHA_NUM);
        if($rml == 'completed')
        {
            $parameters['status_message'] = esc_html__('Password has been changed. You can now log in.', 'read-more-login');
        }

        return parent::DrawView($parameters);
    }
}
