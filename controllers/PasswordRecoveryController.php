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

use WP_PluginFramework\Utils\SecurityFilter;
use WP_PluginFramework\HtmlComponents\StatusBar;
use WP_PluginFramework\HtmlElements\Strong;
use WP_PluginFramework\Utils\DebugLogger;

class PasswordRecoveryController extends MembershipController
{
    /** @var PasswordRecoveryView | PasswordChangeView*/
    public $View;

    private $insert_data_if_secret_ok = false;

    public function __construct()
    {
        $secret = null;
        $secret_required=false;
        $set_event = null;

        $action = SecurityFilter::SafeReadPostRequest('action', SecurityFilter::STRING_KEY_NAME);
        if($action == ReadMoreLoginPlugin::WP_PLUGIN_FRAMEWORK_AJAX_HANDLER)
        {
            $event  = SecurityFilter::SafeReadPostRequest('_event', SecurityFilter::STRING_KEY_NAME);

            $reg_id = SecurityFilter::SafeReadPostRequest(self::REG_ID, SecurityFilter::POSITIVE_INTEGER_ZERO);
            $nonce  = SecurityFilter::SafeReadPostRequest(self::REG_NONCE, SecurityFilter::ALPHA_NUM);
            $post_id = SecurityFilter::SafeReadPostRequest(self::REG_POST_ID, SecurityFilter::POSITIVE_INTEGER);
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
                $data = $this->DecodeEmailUrlData($rml);

                if(isset($data))
                {
                    $event = 'PasswordLink';
                    $set_event = $event;

                    $reg_id = SecurityFilter::SafeReadArrayItem($data, 'r', SecurityFilter::POSITIVE_INTEGER_ZERO);
                    $secret = SecurityFilter::SafeReadArrayItem($data, 's', SecurityFilter::ALPHA_NUM);
                    $secret_required = true;

                    $this->insert_data_if_secret_ok = true;
                }
            }

            $post_id = get_the_ID();
        }

        $reg_type = RegistrationDbTable::REG_TYPE_PASSWORD_RECOVERY;

        parent::__construct('ARU_ReadMoreLogin\PasswordRecoveryView', $event, $reg_id, $reg_type, $post_id, $nonce, $secret, $secret_required);

        if(isset($set_event))
        {
            $this->SetEvent($set_event);
        }

        $this->RegisterEvent('InitPasswordChange', 'init', 'post');
    }

    public function InitPasswordChange_init($event)
    {
        if (!is_user_logged_in())
        {
            if($event === 'ButtonSavePassword_click')
            {
                $password1 = $this->View->Password1->GetText();
                $password2 = $this->View->Password2->GetText();

                switch ($this->GetState())
                {
                    case RegistrationDbTable::STATE_RESET_PASSWORD_EMAIL_CONFIRM;
                        if ($this->CheckPasswordChangeData($password1, $password2))
                        {
                            $wp_user_id = $this->GetWpUserId();
                            wp_set_password($password1, $wp_user_id);

                            $this->SetState(RegistrationDbTable::STATE_RESET_PASSWORD_DONE);

                            $linking_options = new SettingsLinkingOptions();
                            $login_url = $linking_options->GetLoginUrl();
                            $login_url = add_query_arg('rml_password', 'completed', $login_url);
                            wp_redirect($login_url);
                            exit();
                        }
                        break;

                    default:
                        DebugLogger::WriteDebugError('Unhandled state ' . $this->GetState());
                }
            }
        }
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

                    $this->RegisterEvent('PasswordLink', 'get', 'get');
                    $this->SetEvent('PasswordLink');
                }
            }
        }

        return $nonce_protected_data;
    }

    public function ButtonSendPassword_click()
    {
        $this->HideInputErrorIndications();

        if(!is_user_logged_in())
        {
            $email = $this->View->Email->GetText();

            switch ($this->GetState())
            {
                case RegistrationDbTable::STATE_REGISTRATION_NOT_SET:
                    $wp_user_id = $this->CheckSendPasswordLinkData($email);
                    if($wp_user_id !== false)
                    {
                        if($this->SendEmailResetLink($email, $wp_user_id))
                        {
                            $this->SetState(RegistrationDbTable::STATE_RESET_PASSWORD_EMAIL_SENT);

                            $this->ReloadView('ARU_ReadMoreLogin\GeneralMessageView');
                            $s = new Strong($email);
                            $formatted_email = $s->DrawHtml();
                            /* translators: %s: E-mail address. */
                            $msg = sprintf(esc_html__('E-mail sent to %s with username and password reset link.', 'read-more-login'), $formatted_email);
                            $this->View->StatusBarHeader->SetStatusHtml($msg, StatusBar::STATUS_SUCCESS);

                            $msg = esc_html__('In case you don\'t see the e-mail, also check your e-mail application\'s spam folder.', 'read-more-login');
                            $this->View->StatusBarFooter->SetStatusText($msg, StatusBar::STATUS_SUCCESS);
                        }
                        else
                        {
                            $s = new Strong($email);
                            $formatted_email = $s->DrawHtml();
                            /* translators: %s: E-mail address. */
                            $msg = sprintf(esc_html__('Error sending e-mail to %s. Retry later or contact site admin if problem persists.', 'read-more-login'), $formatted_email);
                            $this->View->StatusBarFooter->SetStatusHtml($msg, StatusBar::STATUS_ERROR);
                        }
                    }
                    else
                    {
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

    public function ButtonSavePassword_click()
    {
        $this->HideInputErrorIndications();

        if(!is_user_logged_in())
        {
            $password1 = $this->View->Password1->GetText();
            $password2 = $this->View->Password2->GetText();

            switch ($this->GetState())
            {
                case RegistrationDbTable::STATE_RESET_PASSWORD_EMAIL_CONFIRM;
                    if($this->CheckPasswordChangeData($password1, $password2))
                    {
                        $wp_user_id = $this->GetWpUserId();
                        wp_set_password($password1, $wp_user_id);

                        $this->SetState(RegistrationDbTable::STATE_RESET_PASSWORD_DONE);

                        $controller = new LoginController();
                        $this->ReloadController($controller);
                        $this->View->StatusBarHeader->SetStatusText(esc_html__('Password has been changed. You can now log in.', 'read-more-login'), StatusBar::STATUS_SUCCESS);
                    }
                    else
                    {
                        $message = $this->GetServerContextData('response_message');
                        $this->View->StatusBarFooter->SetStatusText($message, StatusBar::STATUS_ERROR);
                    }
                    break;

                case RegistrationDbTable::STATE_RESET_PASSWORD_DONE:
                    $controller = new LoginController();
                    $this->ReloadController($controller);
                    $controller->View->StatusBarFooter->SetStatusText(esc_html__('This password reset link has been used. The link can only be used once. You can now log in or you need to request a new password reset link.', 'read-more-login'), StatusBar::STATUS_SUCCESS);
                    break;

                case RegistrationDbTable::STATE_RESET_PASSWORD_EXPIRED:
                    $this->View->StatusBarFooter->SetStatusText(esc_html__('This password reset link has expired. You must request a new e-mail with a new reset link.', 'read-more-login'), StatusBar::STATUS_ERROR);
                    break;

                default:
                    DebugLogger::WriteDebugError('Unhandled state ' . $this->GetState());

            }
        }

        $this->ShowInputErrorIndications();
    }

    public function PasswordLink_get()
    {
        $this->HideInputErrorIndications();

        if(!is_user_logged_in())
        {
            switch ($this->GetState())
            {
                case RegistrationDbTable::STATE_RESET_PASSWORD_EMAIL_SENT:
                case RegistrationDbTable::STATE_RESET_PASSWORD_EMAIL_CONFIRM:
                    $wp_user_id = $this->GetWpUserId();
                    if ($wp_user_id)
                    {
                        $this->SetInitCallback('InitPasswordChange');
                        $this->ReloadView('ARU_ReadMoreLogin\PasswordRecoveryChangeView');
                        $s = new Strong($this->GetUsername());
                        $formatted_username = $s->DrawHtml();
                        /* translators: %s: Username. */
                        $msg = sprintf(esc_html__('Select a new password for username %s.', 'read-more-login'), $formatted_username);
                        $this->View->StatusBarHeader->SetStatusHtml($msg, StatusBar::STATUS_SUCCESS);
                    }
                    else
                    {
                        $s = new Strong($this->GetEmail());
                        $formatted_email = $s->DrawHtml();
                        /* translators: %s: E-mail address. */
                        $msg = sprintf(esc_html__('No user with e-mail address %s.', 'read-more-login'), $formatted_email);
                        $this->View->StatusBarFooter->SetStatusHtml($msg, StatusBar::STATUS_ERROR);
                    }
                    $this->SetState(RegistrationDbTable::STATE_RESET_PASSWORD_EMAIL_CONFIRM);
                    break;

                case RegistrationDbTable::STATE_RESET_PASSWORD_DONE:
                    $reg_type = RegistrationDbTable::REG_TYPE_PASSWORD_RECOVERY;
                    $post_id = get_the_ID();
                    $this->CreateNewRegistrationRecord($reg_type, $post_id);
                    $this->ReloadView();
                    $this->View->StatusBarHeader->SetStatusText(esc_html__('This password reset link has been used. The link can only be used once. You can now log in or you need to request a new password reset link.', 'read-more-login'), StatusBar::STATUS_ERROR);
                    break;

                case RegistrationDbTable::STATE_RESET_PASSWORD_EXPIRED:
                    $reg_type = RegistrationDbTable::REG_TYPE_PASSWORD_RECOVERY;
                    $post_id = get_the_ID();
                    $this->CreateNewRegistrationRecord($reg_type, $post_id);
                    $this->ReloadView();
                    $this->View->StatusBarHeader->SetStatusText(esc_html__('This password reset link has been used. The link can only be used once. You can now log in or you need to request a new password reset link.', 'read-more-login'), StatusBar::STATUS_ERROR);
                    break;

                default:
                    DebugLogger::WriteDebugError('Unhandled state ' . $this->GetState());
            }
        }

        $this->ShowInputErrorIndications();
    }
}
