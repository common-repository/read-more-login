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

use WP_PluginFramework\HtmlElements\Strong;
use WP_PluginFramework\Utils\SecurityFilter;
use WP_PluginFramework\HtmlComponents\StatusBar;
use WP_PluginFramework\Utils\DebugLogger;

class RegistrationController extends MembershipController
{
    /** @var RegistrationView | RegisterUserDataView */
    public $View;

    private $insert_data_if_secret_ok = false;

    public function __construct()
    {
        $secret = null;
        $secret_required=false;

        $action = SecurityFilter::SafeReadPostRequest('action', SecurityFilter::STRING_KEY_NAME);
        if($action == ReadMoreLoginPlugin::WP_PLUGIN_FRAMEWORK_AJAX_HANDLER)
        {
            $event  = SecurityFilter::SafeReadPostRequest(self::REG_EVENT, SecurityFilter::STRING_KEY_NAME);
            $reg_id = SecurityFilter::SafeReadPostRequest(self::REG_ID, SecurityFilter::POSITIVE_INTEGER_ZERO);
            $post_id = SecurityFilter::SafeReadPostRequest(self::REG_POST_ID, SecurityFilter::POSITIVE_INTEGER);
            $nonce  = SecurityFilter::SafeReadPostRequest(self::REG_NONCE, SecurityFilter::ALPHA_NUM);
        }
        else
        {
            $event = null;
            $reg_id = null;
            $nonce  = null;

            /* The link code can contain alphanumeric and hyphen and underscore characters. */
            $rml = SecurityFilter::SafeReadGetRequest('rml', SecurityFilter::STRING_KEY_NAME);

            if(isset($rml))
            {
                /* Don't flood the debug log due to hacking attempt. Write to buffer until nonce confirmed. */
                DebugLogger::PauseWpDebugLogging();

                $data = $this->DecodeEmailUrlData($rml);

                if(isset($data))
                {
                    $event = 'ConfirmEmail';

                    $reg_id = SecurityFilter::SafeReadArrayItem($data, 'r', SecurityFilter::POSITIVE_INTEGER_ZERO);
                    $secret = SecurityFilter::SafeReadArrayItem($data, 's', SecurityFilter::ALPHA_NUM);
                    $secret_required=true;

                    $this->insert_data_if_secret_ok = true;
                }
            }

            $post_id = get_the_ID();
        }

        $reg_type = RegistrationDbTable::REG_TYPE_USER_REGISTRATION;

        parent::__construct('ARU_ReadMoreLogin\RegistrationView', $event, $reg_id, $reg_type, $post_id, $nonce, $secret, $secret_required);

        $this->SetInitCallback('Registering');
        $this->RegisterEvent('Registering', 'init', 'post');
    }

    protected function ReadNonceProtectedData($event_source)
    {
        $nonce_protected_data = parent::ReadNonceProtectedData($event_source);

        if(!$nonce_protected_data)
        {
            /* Get url contains no nonce protected data. Must recreate it before executing events. The events check for nonce.  */
            if ($this->insert_data_if_secret_ok)
            {
                if ($this->HasUserData())
                {
                    $nonce_protected_data = $this->GetNonceProtectedData();
                    $nonce_protected_data = $this->CalculateWpNonce($nonce_protected_data);

                    $this->RegisterEvent('ConfirmEmail', 'get', 'get');
                    $this->SetEvent('ConfirmEmail');
                }
            }
        }

        return $nonce_protected_data;
    }

    public function Registering_init($event)
    {
        if (is_user_logged_in())
        {
            $current_wp_user = get_current_user_id();
            $registered_wp_user = $this->GetWpUserId();
            if($current_wp_user !== $registered_wp_user)
            {
                $this->LogOutUser();
            }
        }

        if (!is_user_logged_in())
        {
            if($event === 'ButtonRegister_click')
            {
                switch ($this->GetState())
                {
                    case RegistrationDbTable::STATE_REGISTRATION_EMAIL_SENT:
                    case RegistrationDbTable::STATE_REGISTRATION_EMAIL_CONFIRMED:
                        $username = $this->View->Username->GetText();
                        $password = $this->View->Password->GetText();

                        if($this->CheckRegisterUserData($username, $password))
                        {
                            if($this->RegisterUsernamePassword($username, $password))
                            {
                                if ($this->HasAllRequiredInfo())
                                {
                                    if (!$this->EmailExist())
                                    {
                                        if (!$this->UserExist())
                                        {
                                            $first_name = null;
                                            $last_name = null;

                                            if ($this->CheckAndSaveName())
                                            {
                                                if ($this->CreateNewUser())
                                                {
                                                    $post_id = $this->GetPageId();
                                                    Statistics::StatisticsCompleted($post_id);

                                                    $linking_options = new SettingsLinkingOptions();
                                                    $login_url = $linking_options->GetLoginUrl();
                                                    $login_url = add_query_arg( 'rml_registering', 'completed', $login_url);

                                                    wp_redirect($login_url);
                                                    exit();
                                                }
                                                else
                                                {
                                                    $this->ResponseSetError(esc_html__('Username has already been registered. Log in or register using another e-mail.', 'read-more-login'));
                                                }
                                            }
                                        }
                                        else
                                        {
                                            $msg = esc_html__('Username already taken. Please select another username.', 'read-more-login');
                                            $this->ResponseSetError($msg);
                                        }
                                    }
                                    else
                                    {
                                        $email = $this->GetEmail();
                                        $linking_options = new SettingsLinkingOptions();
                                        $login_url = $linking_options->GetLoginUrl();
                                        $login_url = add_query_arg( 'rml_registering', 'emailexist', $login_url);
                                        $login_url = add_query_arg( 'rml_email', $email, $login_url);
                                        wp_redirect($login_url);
                                        exit();
                                    }
                                }
                                else
                                {
                                    $this->ResponseSetError(esc_html__('Error registering. Please retry or contact site admin if problem persists.', 'read-more-login'));
                                }
                            }
                            else
                            {
                                $this->ResponseSetError('Server error.');
                            }
                        }
                        break;

                    case RegistrationDbTable::STATE_REGISTRATION_USER_CREATED:
                        $linking_options = new SettingsLinkingOptions();
                        $login_url = $linking_options->GetLoginUrl();
                        $login_url = add_query_arg( 'rml_registering', 'completed', $login_url);
                        wp_redirect($login_url);
                        exit();
                        break;

                    default:
                        DebugLogger::WriteDebugError('Unhandled state ' . $this->GetState());
                }
            }
            elseif($event === 'ConfirmEmail_get')
            {
                switch ($this->GetState())
                {
                    case RegistrationDbTable::STATE_REGISTRATION_EMAIL_SENT:
                        if ($this->EmailExist())
                        {
                            $email = $this->GetEmail();
                            $linking_options = new SettingsLinkingOptions();
                            $login_url = $linking_options->GetLoginUrl();
                            $login_url = add_query_arg( 'rml_registering', 'emailexist', $login_url);
                            $login_url = add_query_arg( 'rml_email', $email, $login_url);
                            wp_redirect($login_url);
                            exit();
                        }
                        break;

                    case RegistrationDbTable::STATE_REGISTRATION_USER_CREATED:
                        $linking_options = new SettingsLinkingOptions();
                        $login_url = $linking_options->GetLoginUrl();
                        $login_url = add_query_arg( 'rml_registering', 'completed', $login_url);
                        wp_redirect($login_url);
                        exit();
                        break;

                    default:
                        DebugLogger::WriteDebugError('Unhandled state ' . $this->GetState());
                }
            }
        }
    }

    public function ButtonStartRegistration_click()
    {
        $this->HideInputErrorIndications();

        $email = $this->View->Email->GetText();
        $terms_accepted = $this->ReadTermsFromView();
        $email = trim($email);

        if ($this->CheckRegistrationEmailData($email))
        {
            if ($this->CheckRegistrationTermsAccepted($terms_accepted))
            {
                if ($this->RegisterEmail($email))
                {
                    $this->SetState(RegistrationDbTable::STATE_REGISTRATION_EMAIL_SENT);

                    $controller = new GeneralMessageController();
                    $this->ReloadController($controller);
                    $s = new Strong($email);
                    $formatted_email = $s->DrawHtml();
                    /* translators: %s: E-mail address. */
                    $msg = sprintf(esc_html__('An e-mail has been sent to %s. Please check your e-mail.', 'read-more-login'), $formatted_email);
                    $controller->View->StatusBarFooter->SetStatusHtml($msg, StatusBar::STATUS_SUCCESS);

                    $post_id = $this->GetPageId();
                    Statistics::StatisticsRegister($post_id);
                    Statistics::StatisticsVerifyEmail($post_id);
                }
                else
                {
                    $message = $this->GetErrorMessage();
                    /* This message may have email in <strong>, use Html setter */
                    $this->View->StatusBarFooter->SetStatusHtml($message, StatusBar::STATUS_ERROR);
                }
            }
            else
            {
                $message = $this->GetServerContextData('response_message');
                $this->View->StatusBarFooter->SetStatusText($message, StatusBar::STATUS_ERROR);
            }
        }
        else
        {
            $message = $this->GetServerContextData('response_message');
            $this->View->StatusBarFooter->SetStatusText($message, StatusBar::STATUS_ERROR);
        }

        $this->ShowInputErrorIndications();
    }

    public function ConfirmEmail_get()
    {
        if(is_user_logged_in())
        {
            switch ($this->GetState())
            {
                case RegistrationDbTable::STATE_REGISTRATION_USER_CREATED:
                    $controller = new LoginController();
                    $this->ReloadController($controller);
                    $controller->View->StatusBarHeader->SetStatusText(esc_html__('Registration link has been used and user created.', 'read-more-login'), StatusBar::STATUS_SUCCESS);
                    break;

                default:
                    DebugLogger::WriteDebugError('Unhandled state ' . $this->GetState());
            }
        }
        else
        {
            switch ($this->GetState())
            {
                case RegistrationDbTable::STATE_REGISTRATION_EMAIL_SENT:
                case RegistrationDbTable::STATE_REGISTRATION_EMAIL_CONFIRMED:
                    if (!$this->EmailExist())
                    {

                        $this->ReloadView('ARU_ReadMoreLogin\RegisterUserDataView');
                        $s = new Strong($this->GetEmail());
                        $formatted_email = $s->DrawHtml();
                        /* translators: %s: E-mail address. */
                        $msg = sprintf(esc_html__('You are registering using e-mail address %s', 'read-more-login'), $formatted_email);
                        $this->View->StatusBarHeader->SetStatusHtml($msg, StatusBar::STATUS_SUCCESS);
                    }
                    else
                    {
                        $email = $this->GetEmail();

                        /* Create a new registration record to remove old registration data. However, not saved. */
                        $this->CreateNewRegistrationRecord(RegistrationDbTable::REG_TYPE_USER_REGISTRATION, get_the_ID());

                        $controller = new LoginController();
                        $this->ReloadController($controller);
                        $s = new Strong($email);
                        $formatted_email = $s->DrawHtml();
                        /* translators: %s: E-mail address. */
                        $msg = sprintf(esc_html__('User account with e-mail %s are already registered. You can now log in.', 'read-more-login'), $formatted_email);
                        $controller->View->StatusBarHeader->SetStatusHtml($msg, StatusBar::STATUS_ERROR);
                    }
                    break;

                    /*
                case RegistrationDbTable::STATE_REGISTRATION_USER_CREATED:
                    $controller = new LoginController();
                    $this->ReloadController($controller);
                    $msg = esc_html__('User already created. You can now log in.', 'read-more-login');
                    $controller->View->StatusBarHeader->SetText($msg, StatusBar::STATUS_SUCCESS);
                    */

                default:
                    DebugLogger::WriteDebugError('Unhandled state ' . $this->GetState());
            }
        }
    }

    public function ButtonRegister_click()
    {
        $this->HideInputErrorIndications();

        if(!is_user_logged_in())
        {
            switch ($this->GetState())
            {
                case RegistrationDbTable::STATE_REGISTRATION_EMAIL_SENT:
                case RegistrationDbTable::STATE_REGISTRATION_EMAIL_CONFIRMED:
                    if ($this->EmailExist())
                    {
                        $this->ReloadView('ARU_ReadMoreLogin\RegistrationView');
                        $message = $this->GetServerContextData('response_message');
                        $this->View->StatusBarHeader->SetStatusText($message, StatusBar::STATUS_ERROR);
                    }
                    else
                    {
                        $s = new Strong($this->GetEmail());
                        $formatted_email = $s->DrawHtml();
                        /* translators: %s: E-mail address. */
                        $msg = sprintf(esc_html__('You are registering using e-mail address %s', 'read-more-login'), $formatted_email);
                        $this->View->StatusBarHeader->SetStatusHtml($msg, StatusBar::STATUS_SUCCESS);

                        $message = $this->GetServerContextData('response_message');
                        $this->View->StatusBarFooter->SetStatusText($message, StatusBar::STATUS_ERROR);
                    }
                    break;

                default:
                    DebugLogger::WriteDebugError('Unhandled state ' . $this->GetState());
            }
        }

        $this->ShowInputErrorIndications();
    }

    public function DrawView($parameters=null)
    {
        if(!is_user_logged_in())
        {
            $post_id = $this->GetPageId();
            Statistics::StatisticsPageView($post_id);
        }

        return parent::DrawView($parameters);
    }
}
