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

use WP_PluginFramework\Controllers\StdController;
use WP_PluginFramework\HtmlElements\Strong;
use WP_PluginFramework\Utils\Mailer;
use WP_PluginFramework\Utils\Misc;
use WP_PluginFramework\Utils\SecurityFilter;
use WP_PluginFramework\Utils\DebugLogger;

abstract class MembershipController extends StdController
{
    const ARU_READMORELOGIN_REGISTRATION_COOKIE_NAME = 'readmorelogin_registration';

    const COOKIE_LENGTH =10;
    const NONCE_LENGTH =10;
    const SECRET_LENGTH =20;

    // Constants for AJAX POST and GET requests
    const REG_ID ='rid';
    const REG_TYPE ='rt_ype';
    const REG_NONCE = 'nonce';
    const REG_EVENT = '_event';
    const REG_USERNAME ='username';
    const REG_FIRST_NAME = 'first_name';
    const REG_LAST_NAME ='last_name';
    const REG_PASSWORD ='password';
    const REG_CONFIRM_PW ='confirm_password';
    const REG_REMEMBER ='remember';
    const REG_ACCEPT_TERMS ='accept_terms';
    const REG_EMAIL = 'email';
    const REG_POST_ID = 'post_id';
    const REG_ERROR_MSG = 'error_message';
    const REG_SECRET ='secret';

    // Event field values:
    const REG_EVENT_NONE ='none_event';
    const REG_EVENT_LOGIN = 'login';
    const REG_EVENT_LOGOUT ='logout';
    const REG_EVENT_USER_REGISTER ='register_user';
    const REG_EVENT_READMORE_REGISTER = 'register_readmore';
    const REG_EVENT_RESEND_EMAIL = 'resend_register';
    const REG_EVENT_REGISTER_EMAIL = 'register_email';
    const REG_EVENT_GOTO_LOGIN = 'goto_login';
    const REG_EVENT_CONFIRM_EMAIL ='ConfirmEmail';      // Used by the login form in a post
    const REG_EVENT_CONFIRM_EMAIL_REG = 'confirm_email_reg'; // Used by the single registration form
    const REG_EVENT_UPDATE_PROFILE = 'update_profile';
    const REG_EVENT_CHANGE_PASSWORD ='change_password';
    const REG_EVENT_SEND_RESET_LINK ='reset_password';
    const REG_EVENT_PASSWORD_LINK ='PasswordLink';


    // Response field constants:
    const REG_RESP_RESULT = 'result';      // Mandatory field
    const REG_RESP_ACTION = 'action';      // Optional field
    const REG_RESP_FORM = 'form';        // Mandatory of REG_RESP_TYPE is set
    const REG_RESP_STATUS = 'status';      // Logged-in status
    const REG_RESP_MSG ='message';     // Mandatory field

    // REG_RESP_RESULT values:
    const REG_RESP_RESULT_OK = 'OK';
    const REG_RESP_RESULT_ERROR ='ERROR';

    // REG_RESP_TYPE values:
    const REG_RESP_ACTION_LOAD_FORM ='load_form';

    // REG_RESP_STATUS values:
    const REG_RESP_STATUS_LOGGED_IN ='logged_in';
    const REG_RESP_STATUS_LOGGED_OUT = 'logged_out';

    // Form text fields:
    const TEXT_FIELD_HEADER ='header';
    const TEXT_FIELD_DESCRIPTION = 'description';
    const TEXT_FIELD_GREEN_MSG = 'green_msg';
    const TEXT_FIELD_RED_MSG = 'red_msg';
    const TEXT_FIELD_ERROR_MSG = 'error_message';
    const TEXT_FIELD_SUCCESS_MSG = 'success_message';

    // Status line color classes
    const GREEN_MESSAGE_CLASS ='aru_rml_status_good';
    const RED_MESSAGE_CLASS ='aru_rml_status_error';

    const RESULT_OK = 0;
    const RESULT_NONCE_ERROR = 1;
    const RESULT_CONFIRM_INVALID_LINK = 2;
    const RESULT_ERROR_UNDEFINED = 3;
    const RESULT_CONFIRM_IS_DONE = 4;
    const RESULT_USER_EXIST = 5;
    const RESULT_CONFIRM_EXPIRED_LINK = 6;

    /** @var LoginView | LogoutView */
    public $View;

    //private $registration_data = null;
    private $has_data = false;
    private $error_message = '';

    private $response_result;
    private $response_read_more;
    private $response_form;
    private $response_messages;
    private $response_status;

    protected $TestMode = false;

    public function __construct($view, $event, $reg_id, $reg_type, $post_id, $nonce, $secret, $secret_required)
    {
        parent::__construct('ARU_ReadMoreLogin\RegistrationDbTable', $view);

        if(isset($event))
        {
            $this->has_data = false;

            if ($reg_id != null)
            {
                if ($this->Model->LoadData(RegistrationDbTable::PRIMARY_KEY, $reg_id))
                {
                    $this->has_data = false;

                    if($secret_required)
                    {
                        /* Don't require nonce from e-mail verification links. */
                        $my_secret = $this->Model->GetData(RegistrationDbTable::SECRET);
                        if ($my_secret != '')
                        {
                            if ($my_secret == $secret)
                            {
                                $this->has_data = true;
                            }
                        }
                    }
                    else
                    {
                        $my_nonce = $this->Model->GetData(RegistrationDbTable::NONCE);
                        if ($my_nonce != '')
                        {
                            if ($my_nonce == $nonce)
                            {
                                $this->has_data = true;
                            }
                        }
                    }

                    $state = $this->Model->GetData(RegistrationDbTable::STATE);
                    if($state == RegistrationDbTable::STATE_REGISTRATION_EXPIRED
                        || $state == RegistrationDbTable::STATE_RESET_PASSWORD_EXPIRED)
                    {
                        $this->has_data = $this->CreateRegistrationRecord($reg_type, $post_id);

                        $this->Model->SetData(RegistrationDbTable::STATE, $state);
                    }
                }
                else
                {
                    /* No registration record found, it may have been deleted due to expiration. */
                    $this->has_data = $this->CreateRegistrationRecord($reg_type, $post_id);
                }
            }
            else
            {
                if (isset($nonce) && isset($reg_type) && isset($post_id))
                {
                    $nonce = $this->CheckNonceChanged($nonce);

                    $nonce_string = $this->FormatNonceString($reg_type, $post_id);

                    if(wp_verify_nonce($nonce, $nonce_string))
                    {
                        $this->Model->SetData(RegistrationDbTable::NONCE, $nonce);

                        $cookie = $this->MembershipGetCookie();
                        $this->Model->SetData(RegistrationDbTable::COOKIE, $cookie);
                        $this->Model->SetData(RegistrationDbTable::TIMESTAMP, time());
                        $this->Model->SetData(RegistrationDbTable::REG_TYPE, $reg_type);
                        $this->Model->SetData(RegistrationDbTable::POST_ID, $post_id);
                        $this->has_data = true;
                    }
                    else
                    {
                        $this->Model = new RegistrationDbTable();
                    }
                }
                else
                {
                    $this->Model = new RegistrationDbTable();
                }
            }

            if ($this->has_data == false)
            {
                /* Recreate object to destroy existing object with user data */
                $this->Model = new RegistrationDbTable();
            }
        }
        else
        {
            if($this->TestMode)
            {
            }
            else
            {
                $this->has_data = $this->CreateRegistrationRecord($reg_type, $post_id);
            }
        }

        $this->SetPermission(false);
    }

    protected function AddHiddenFields()
    {
        $post_id = $this->GetPageId();
        if($post_id)
        {
            $this->View->AddHiddenFields(self::REG_POST_ID, $post_id);
        }

        $reg_id = $this->GetRegId();
        if($reg_id)
        {
            $this->View->AddHiddenFields(self::REG_ID, $reg_id);
        }

        $reg_type = $this->GetRegType();
        if($reg_type)
        {
            $this->View->AddHiddenFields(self::REG_TYPE, $reg_type);
        }

        $nonce = $this->GetNonce();
        if($nonce)
        {
            $this->View->AddHiddenFields(self::REG_NONCE, $nonce);
        }
    }

    protected function CreateNewRegistrationRecord($reg_type, $post_id)
    {
        $this->Model = new RegistrationDbTable();

        return $this->CreateRegistrationRecord($reg_type, $post_id);
    }

    public function MembershipGetCookie()
    {
        return SecurityFilter::SafeReadCookieString(self::ARU_READMORELOGIN_REGISTRATION_COOKIE_NAME, SecurityFilter::ALPHA_NUM);
    }

    private function CreateRegistrationRecord($reg_type, $post_id)
    {
        $nonce = $this->CreateNonce($reg_type, $post_id);

        if($nonce)
        {
            $this->Model->SetData(RegistrationDbTable::NONCE, $nonce);

            $cookie = $this->MembershipGetCookie();
            $this->Model->SetData(RegistrationDbTable::COOKIE, $cookie);
            $this->Model->SetData(RegistrationDbTable::TIMESTAMP, time());
            $this->Model->SetData(RegistrationDbTable::REG_TYPE, $reg_type);
            $this->Model->SetData(RegistrationDbTable::POST_ID, $post_id);

            return true;
        }
        else
        {
            return false;
        }
    }

    private function UpdateNonceChanged($nonce)
    {
        /* After logout and login the nonce for the current session is not valid any more. The login state is changed
           during the init phase, and nonce needs to be updated before executing code in handlers. Create a new nonce
           and store it for nonce verification during this http request. */
        if($nonce)
        {
            $reg_type = $this->GetRegType();
            $post_id = $this->GetPageId();
            $new_nonce = $this->CreateNonce($reg_type, $post_id);
            if ($new_nonce)
            {
                $key = 'aru_rml_new_nonce_' . $nonce;
                $GLOBALS[$key] = $new_nonce;
            }

            $new_wp_nonce = $this->CreateWpNonce();
            if ($new_wp_nonce)
            {
                $this->SetServerContextData('new_wp_nonce', $new_wp_nonce);
            }
        }
    }

    protected function CheckWpNonce($nonce_protected_data = array())
    {
        $new_wp_nonce = $this->GetServerContextData('new_wp_nonce');
        if($new_wp_nonce)
        {
            $nonce_protected_data[self::PROTECTED_DATA_WP_NONCE] = $new_wp_nonce;
        }

        return parent::CheckWpNonce($nonce_protected_data);
    }

    private function CheckNonceChanged($nonce)
    {
        /* In case we have been logged in by Init function, the nonce has changed */
        $key = 'aru_rml_new_nonce_' . $nonce;
        if(isset($GLOBALS[$key]))
        {
            /* New nonce has been created by Init function during the login */
            $nonce = $GLOBALS[$key];
        }
        return $nonce;
    }

    protected function CreateNonce($reg_type, $post_id)
    {
        $nonce_string = $this->FormatNonceString($reg_type, $post_id);

        if($nonce_string)
        {
            $nonce = wp_create_nonce($nonce_string);
            return $nonce;
        }

        return false;
    }

    private function FormatNonceString($reg_type, $post_id)
    {
        if(is_numeric($reg_type) and is_numeric($post_id))
        {
            $nonce_string = 'readmore-' . $reg_type . '-' . $post_id;
            return $nonce_string;
        }
        else
        {
            if(!is_numeric($reg_type)){
                DebugLogger::WriteDebugError('Missing reg_type.');
            }

            if(isset($post_id)){
                DebugLogger::WriteDebugError('Missing post_id. Type=' . gettype($post_id). ' Value=' . $post_id);
            }
            else{
                DebugLogger::WriteDebugError('Missing post_id.');
            }
        }

        return false;
    }

    public function HasUserData()
    {
        return $this->has_data;
    }

    protected function CheckLoginData($username, $password)
    {
        $result = false;

        if(isset($username) and ($username != ''))
        {
            if(validate_username($username))
            {
                if(isset($password) and ($password != ''))
                {
                    $result = true;
                }
                else
                {
                    $this->ResponseSetError(esc_html__('You must enter your password to log in.', 'read-more-login'));
                    $this->ResponseSetInputError('Password');

                }
            }
            else
            {
                $this->ResponseSetError(esc_html__('Error. Entered username has invalid characters or is wrong.', 'read-more-login'));
                $this->ResponseSetInputError('Username');
            }
        }
        else
        {
            if(!$password)
            {
                $this->ResponseSetError(esc_html__('You must enter your username and password to log in.', 'read-more-login'));
                $this->ResponseSetInputError('Username');
                $this->ResponseSetInputError('Password');
            }
            else
            {
                $this->ResponseSetError(esc_html__('You must enter your username to log in.', 'read-more-login'));
                $this->ResponseSetInputError('Username');
            }
        }

        return $result;
    }

    protected function CheckResetEmailSecret()
    {
        DebugLogger::WriteDebugNote();

        switch($this->Model->GetData(RegistrationDbTable::STATE))
        {
            case RegistrationDbTable::STATE_RESET_PASSWORD_EMAIL_SENT:
                $this->Model->SetData(RegistrationDbTable::STATE, RegistrationDbTable::STATE_RESET_PASSWORD_EMAIL_CONFIRM);
                $this->Model->SaveData();
                $result = self::RESULT_OK;
                break;

            case RegistrationDbTable::STATE_RESET_PASSWORD_EMAIL_CONFIRM:
                $result = self::RESULT_OK;
                break;

            case RegistrationDbTable::STATE_RESET_PASSWORD_DONE:
                $result = self::RESULT_CONFIRM_IS_DONE;
                break;

            case RegistrationDbTable::STATE_RESET_PASSWORD_EXPIRED:
                $result = self::RESULT_CONFIRM_EXPIRED_LINK;
                break;

            default:
                $result = self::RESULT_ERROR_UNDEFINED;
                break;
        }

        return $result;
    }

    protected function CheckPasswordChangeData($password, $confirm_password)
    {
        if(($password) and ($password == $confirm_password))
        {
            return true;
        }
        else
        {
            if((!$password) and (!$confirm_password))
            {
                $this->ResponseSetError(esc_html__('You must enter a new password.', 'read-more-login'));
                $this->ResponseSetInputError('Password1');
                $this->ResponseSetInputError('Password2');
            }
            else if(!$password)
            {
                $this->ResponseSetError(esc_html__('You must enter same password in both inputs.', 'read-more-login'));
                $this->ResponseSetInputError('Password1');
            }
            else if(!$confirm_password)
            {
                $this->ResponseSetError(esc_html__('You must enter same password in both inputs.', 'read-more-login'));
                $this->ResponseSetInputError('Password2');
            }
            else
            {
                $this->ResponseSetError(esc_html__('Entered passwords does not match. You must enter same password in both inputs.', 'read-more-login'));
                $this->ResponseSetInputError('Password1');
                $this->ResponseSetInputError('Password2');
            }
        }

        return false;
    }

    protected function CheckSendPasswordLinkData($email)
    {
        if(!$email)
        {
            $this->ResponseSetError(esc_html__('You must enter your e-mail address.', 'read-more-login'));
            $this->ResponseSetInputError('Email');
        }
        else
        {
            if(filter_var($email, FILTER_VALIDATE_EMAIL))
            {
                $wp_user_id = email_exists($email);
                if($wp_user_id)
                {
                    return $wp_user_id;
                }
                else
                {
                    $this->ResponseSetError(esc_html__('Error. This e-mail has no user account. Please enter the e-mail address used during registration.', 'read-more-login'));
                    $this->ResponseSetInputError('Email');
                }
            }
            else
            {
                $this->ResponseSetError(esc_html__('Error. Invalid e-mail address format. Please enter a correct e-mail address.', 'read-more-login'));
                $this->ResponseSetInputError('Email');
            }
        }

        return false;
    }

    protected function CheckRegistrationEmailData($email)
    {
        if($email)
        {
            if(filter_var($email, FILTER_VALIDATE_EMAIL))
            {
                return true;
            }
            else
            {
                $this->ResponseSetError(esc_html__('E-mail address has wrong format. Please write a correct e-mail address.', 'read-more-login'));
                $this->ResponseSetInputError('Email');
            }
        }
        else
        {
            $this->ResponseSetError(esc_html__('You must enter your e-mail address to register.', 'read-more-login'));
            $this->ResponseSetInputError('Email');
        }

        return false;
    }

    protected function CheckRegistrationTermsAccepted($terms_accepted)
    {
        if (isset($terms_accepted) and ($terms_accepted === 1))
        {
            return true;
        }
        else
        {
            $this->ResponseSetError(esc_html__('You must accept the terms and conditions to register.', 'read-more-login'));
            $this->ResponseSetInputError('AcceptTerms');
        }

        return false;
    }

    protected function CheckRegisterUserData($username, $password)
    {
        if ($username and $password)
        {
            if (validate_username($username))
            {
                $register_option = get_option(SettingsSecurityOptions::OPTION_NAME);

                if(($register_option[SettingsSecurityOptions::USERNAME_NOT_EMAIL]) && (filter_var($username, FILTER_VALIDATE_EMAIL)))
                {
                    $this->ResponseSetError(esc_html__('You can not use e-mail as username. Please select another username.', 'read-more-login'));
                    $this->ResponseSetInputError('Username');
                }
                else
                {
                    if (isset($password))
                    {
                        if (!username_exists($username))
                        {
                            return true;
                        }
                        else
                        {
                            $this->ResponseSetError(esc_html__('Error. This username has already been taken. Please select another username.', 'read-more-login'));
                            $this->ResponseSetInputError('Username');
                        }
                    }
                    else
                    {
                        $this->ResponseSetError(esc_html__('You must select a password to register.', 'read-more-login'));
                        $this->ResponseSetInputError('Password');
                    }
                }
            }
            else
            {
                $this->ResponseSetError(esc_html__('Error. Entered username has invalid characters.', 'read-more-login'));
                $this->ResponseSetInputError('Username');
            }
        }
        else
        {
            if (!$username and !$password)
            {
                $this->ResponseSetError(esc_html__('You must select username and password to register.', 'read-more-login'));
                $this->ResponseSetInputError('Username');
                $this->ResponseSetInputError('Password');
            }
            elseif (!$username)
            {
                $this->ResponseSetError(esc_html__('You must select a username to register.', 'read-more-login'));
                $this->ResponseSetInputError('Username');
            }
            elseif (!$password)
            {
                $this->ResponseSetError(esc_html__('You must select a password to register.', 'read-more-login'));
                $this->ResponseSetInputError('Password');
            }
        }

        return false;
    }

    protected function CheckAndSaveName()
    {
        $first_name = '';
        $last_name = '';

        $design_option = get_option(SettingsFormOptions::OPTION_NAME);
        if($design_option[SettingsFormOptions::REGISTER_COLLECT_FIRST_NAME])
        {
            $first_name = $this->View->FirstName->GetText();
        }

        if($design_option[SettingsFormOptions::REGISTER_COLLECT_FIRST_NAME]
            and $design_option[SettingsFormOptions::REGISTER_FIRST_NAME_REQUIRED]
            and (!$first_name))
        {
            /* First name is required but not set. */
            $this->ResponseSetError(esc_html__('First name is required field.', 'read-more-login'));
            $this->ResponseSetInputError('FirstName');

        }
        else
        {
            $design_option = get_option(SettingsFormOptions::OPTION_NAME);
            if($design_option[SettingsFormOptions::REGISTER_COLLECT_LAST_NAME])
            {
                $last_name = $this->View->LastName->GetText();
            }

            if($design_option[SettingsFormOptions::REGISTER_COLLECT_LAST_NAME]
                and $design_option[SettingsFormOptions::REGISTER_LAST_NAME_REQUIRED]
                and (!$last_name))
            {
                /* Last name is required but not set. */
                $this->ResponseSetError(esc_html__('Last name is required field.', 'read-more-login'));
                $this->ResponseSetInputError('LastName');
            }
            else
            {
                $this->Model->SetData(RegistrationDbTable::FIRST_NAME, $first_name);
                $this->Model->SetData(RegistrationDbTable::LAST_NAME, $last_name);
                $result = $this->Model->SaveData();

                if($result === false)
                {
                    $this->ResponseSetError(esc_html__('Undefined error. Illegal characters in name.', 'read-more-login'));
                    $this->ResponseSetInputError('FirstName');
                    $this->ResponseSetInputError('LastName');
                }

                return $result;
            }
        }
        return false;
    }

    protected function RegisterUsernamePassword($username, $password)
    {
        DebugLogger::WriteDebugNote();

        if($username != '' && $password != '')
        {
            $password_hash = wp_hash_password($password);
            $this->Model->SetData(RegistrationDbTable::USERNAME, $username);
            $this->Model->SetData(RegistrationDbTable::PASSWORD, $password_hash);
            if($this->Model->SaveData())
            {
                return true;
            }
        }

        return false;
    }

    public function RegisterEmail($email)
    {
        DebugLogger::WriteDebugNote(DebugLogger::Obfuscate($email));

        $result = false;

        if($email != '')
        {
            $this->Model->SetData(RegistrationDbTable::EMAIL, $email);

            $secret = $this->Model->GetData(RegistrationDbTable::SECRET);
            if($secret == '')
            {
                $secret = Misc::RandomString(self::SECRET_LENGTH);
                $this->Model->SetData(RegistrationDbTable::SECRET, $secret);
            }

            $retry = $this->Model->GetData(RegistrationDbTable::RETRY_COUNTER);
            $retry += 1;
            $this->Model->SetData(RegistrationDbTable::RETRY_COUNTER, $retry);

            $this->Model->SaveData();

            $reg_id = $this->Model->GetData(RegistrationDbTable::PRIMARY_KEY);
            $retry_counter = $this->Model->GetData(RegistrationDbTable::RETRY_COUNTER);
            $post_id = $this->Model->GetData(RegistrationDbTable::POST_ID);

            if($retry_counter < 5)
            {
                $data = array();
                $data['r'] = $reg_id;
                $data['s'] = $secret;
                $url_data = $this->EncodeEmailUrlData($data);

                $verification_link = get_permalink($post_id);
                $verification_link = add_query_arg('rml', $url_data, $verification_link);

                $reg_type = $this->Model->GetData(RegistrationDbTable::REG_TYPE);

                if($reg_type === RegistrationDbTable::REG_TYPE_READ_MORE_REGISTRATION)
                {
                    $result = $this->SendEmailReadMoreVerification($email, $verification_link, $post_id);
                }
                else
                {
                    $result = $this->SendEmailRegisterVerification($email, $verification_link, $post_id);
                }

                if($result === false)
                {
                    $s = new Strong($email);
                    $formatted_email = $s->DrawHtml();
                    /* translators: %s: E-mail address. */
                    $this->error_message = sprintf(esc_html__('Error sending e-mail to %s. Retry later or contact site admin if problem persists.', 'read-more-login'), $formatted_email);
                }

                $this->has_data = true;
            }
            else
            {
                $this->error_message = esc_html__('Error. Maximum number of e-mail sent.', 'read-more-login');
            }
        }

        return $result;
    }

    protected function GetEmailKeyList($empty_value)
    {
        $variables = array();
        $variables['name'] = $empty_value;
        $variables['username'] = $empty_value;
        $variables['first_name'] = $empty_value;
        $variables['last_name'] = $empty_value;
        $variables['email'] = $empty_value;
        $variables['title'] = $empty_value;
        $variables['site_name'] = $empty_value;
        $variables['site_url'] = $empty_value;
        $variables['link'] = $empty_value;
        return $variables;
    }

    protected function PrepareVariables($link, $post_id, $escaping)
    {
        $username = $this->GetUsername();
        $first_name = $this->GetFirstname();
        $last_name = $this->GetLastName();
        $email = $this->GetEmail();

        $variables = $this->GetEmailKeyList('N/A');

        $variables['name'] = $this->GetUsernameOrNames($username, $first_name, $last_name);
        $variables['username'] = $username;
        $variables['first_name'] = $first_name;
        $variables['last_name'] = $last_name;
        $variables['email'] = $email;
        if($post_id)
        {
            if($escaping)
            {
                $variables['title'] = get_the_title($post_id);
            }
            else
            {
                $post = get_post($post_id);
                $variables['title'] = isset($post->post_title) ? $post->post_title : '';
            }
        }
        if($escaping)
        {
            $variables['site_name'] = get_bloginfo('name');
        }
        else
        {
            $variables['site_name'] = html_entity_decode(get_bloginfo('name'), ENT_QUOTES);
        }
        $variables['site_url'] = site_url();
        if($link)
        {
            $variables['link'] = $href = '<a href="' . $link . '">' . $link . '</a>';
        }

        return $variables;
    }

    protected function ReplaceEmailVariables($text, $variables)
    {
        foreach($variables as $variable => $value)
        {
            $text = str_replace('{' . $variable . '}', $value, $text);
            $text = str_replace('%' . $variable . '%', $value, $text);
        }
        return $text;
    }

    private function SendEmailReadMoreVerification($email, $link, $post_id)
    {
        DebugLogger::WriteDebugNote(DebugLogger::Obfuscate($email), $post_id);

        $options = get_option(SettingsEmailOptions::OPTION_NAME);

        $subject = $options[SettingsEmailOptions::READMORE_REGISTER_SUBJECT];
        $body = $options[SettingsEmailOptions::READMORE_REGISTER_BODY];

        $variables = $this->PrepareVariables($link, $post_id, false);
        $subject = $this->ReplaceEmailVariables($subject, $variables);
        $body = $this->ReplaceEmailVariables($body, $variables);

        return $this->SendEmail($email, $options[SettingsEmailOptions::READMORE_REGISTER_REPLY_ADDRESS], $subject, $body);
    }

    private function SendEmailRegisterVerification($email, $link, $post_id)
    {
        DebugLogger::WriteDebugNote(DebugLogger::Obfuscate($email), $post_id);

        $options = get_option(SettingsEmailOptions::OPTION_NAME);

        $subject = $options[SettingsEmailOptions::REGISTER_SUBJECT];
        $body = $options[SettingsEmailOptions::REGISTER_BODY];

        $variables = $this->PrepareVariables($link, $post_id, false);
        $subject = $this->ReplaceEmailVariables($subject, $variables);
        $body = $this->ReplaceEmailVariables($body, $variables);

        return $this->SendEmail($email, $options[SettingsEmailOptions::REGISTER_REPLAY_ADDRESS], $subject, $body);
    }

    public function ConfirmEmail()
    {
        DebugLogger::WriteDebugNote();

        switch($this->Model->GetData(RegistrationDbTable::STATE))
        {
            case RegistrationDbTable::STATE_REGISTRATION_EMAIL_SENT:
                $this->Model->SetData(RegistrationDbTable::STATE, RegistrationDbTable::STATE_REGISTRATION_EMAIL_CONFIRMED);
                $this->Model->SaveData();
                $result = self::RESULT_OK;
                break;

            case RegistrationDbTable::STATE_REGISTRATION_EMAIL_CONFIRMED:
                $result = self::RESULT_CONFIRM_IS_DONE;
                break;

            case RegistrationDbTable::STATE_REGISTRATION_USER_CREATED:
                $result = self::RESULT_USER_EXIST;
                break;

            case RegistrationDbTable::STATE_REGISTRATION_EXPIRED:
                $result = self::RESULT_CONFIRM_EXPIRED_LINK;
                break;

            default:
                $result = self::RESULT_ERROR_UNDEFINED;
                break;
        }

        return $result;
    }

    protected function EncodeEmailUrlData($array_data)
    {
        /* Add random characters in start to obfuscate the string, or it will always start with same characters. */
        $seed = chr(rand(32,122)) . chr(rand(32,122)) . chr(rand(32,122));
        $json = $seed . json_encode($array_data);
        $json_bas64 = base64_encode($json);
        $json_bas64url = rtrim(strtr($json_bas64, '+/', '-_'), '=');
        return $json_bas64url;
    }

    protected function DecodeEmailUrlData($url_data)
    {
        $json = base64_decode(str_pad(strtr($url_data, '-_', '+/'), strlen($url_data) % 4, '=', STR_PAD_RIGHT));
        /* Remove any random characters in front of the json string itself, these are for obfuscating. */
        $json_start_pos = strpos($json, '{');
        $json = substr($json, $json_start_pos);
        $array_data = json_decode($json, true);
        return $array_data;
    }

    protected function SendEmailResetLink($email, $wp_user_id)
    {
        DebugLogger::WriteDebugNote(DebugLogger::Obfuscate($email));

        $options = get_option(SettingsEmailOptions::OPTION_NAME);

        $user_info = get_userdata($wp_user_id);
        $username = $user_info->user_login;

        $secret = Misc::RandomString(self::SECRET_LENGTH);
        $this->Model->SetData(RegistrationDbTable::SECRET, $secret);
        $this->Model->SetData(RegistrationDbTable::STATE, RegistrationDbTable::STATE_RESET_PASSWORD_EMAIL_SENT);
        $this->Model->SetData(RegistrationDbTable::USERNAME, $username);
        $this->Model->SetData(RegistrationDbTable::EMAIL, $email);
        $this->Model->SetData(RegistrationDbTable::WP_USER_ID, $wp_user_id);
        $this->Model->SaveData();
        $reg_id = $this->Model->GetData(RegistrationDbTable::PRIMARY_KEY);

        $post_id = $this->GetPageId();

        $data = array();
        $data['r'] = $reg_id;
        $data['s'] = $secret;
        $url_data = $this->EncodeEmailUrlData($data);

        $link = get_permalink($post_id);
        $link = add_query_arg('rml', $url_data, $link);

        $subject = $options[SettingsEmailOptions::PASSWORD_SUBJECT];
        $body = $options[SettingsEmailOptions::PASSWORD_BODY];

        $variables = $this->PrepareVariables($link, $post_id, false);
        $subject = $this->ReplaceEmailVariables($subject, $variables);
        $body = $this->ReplaceEmailVariables($body, $variables);

        return $this->SendEmail($email, $options[SettingsEmailOptions::PASSWORD_REPLAY_ADDRESS], $subject, $body);
    }

    protected function SendEmailNotificationNewUser($wp_user_id)
    {
        DebugLogger::WriteDebugNote();

        $options = get_option(SettingsEmailOptions::OPTION_NAME);

        if($options[SettingsEmailOptions::NOTIFICATION_ENABLE] and $options[SettingsEmailOptions::NOTIFICATION_SEND_TO] != '')
        {
            $user_info = get_userdata($wp_user_id);
            $username  = $user_info->user_login;

            $subject = $options[SettingsEmailOptions::NOTIFICATION_SUBJECT];
            $body = $options[SettingsEmailOptions::NOTIFICATION_BODY];

            $post_id = $this->GetPageId();
            $variables = $this->PrepareVariables(null, $post_id, false);
            $variables['username'] = $username;

            $subject = $this->ReplaceEmailVariables($subject, $variables);
            $body = $this->ReplaceEmailVariables($body, $variables);

            return $this->SendEmail(
                $options[SettingsEmailOptions::NOTIFICATION_SEND_TO],
                $options[SettingsEmailOptions::NOTIFICATION_REPLAY_ADDRESS],
                $subject,
                $body
            );
        }
    }

    private function SendEmail($to, $from, $subject, $body)
    {
        $email = array(
            'to'=> $to,
            'from' => $from,
            'subject' => $subject,
            'body' => $body
        );

        $email = apply_filters('readmorelogin_email', $email);

        if(gettype($email) == 'array')
        {
            $mailer = new Mailer($email['to']);
            $mailer->SetFromAddress($email['from']);
            $mailer->SetSubject($email['subject']);
            $mailer->SetBody($email['body']);
            return $mailer->Send();
        }

        return ($email == true);
    }

    protected function UpdatePassword($password, $wp_user_id)
    {
        DebugLogger::WriteDebugNote();

        if(is_user_logged_in())
        {
            /* Need to catch the cookie used for authentication. The cookie will be set by wp_set_auth_cookie.
               This will not be installed until next browser request, and we may need it for nonce generation. */
            add_action('set_logged_in_cookie', 'ARU_ReadMoreLogin\MembershipController::InstallLoggedInCookie');
        }

        $user_data['ID'] = $wp_user_id;
        $user_data['user_pass'] = $password;
        $user_id = wp_update_user( $user_data );

        if ( is_wp_error( $user_id ) )
        {
            return false;
        }
        else
        {
            if(is_user_logged_in())
            {
                /* After password change the nonce for this session is not valid any more. */
                $old_nonce = $this->GetNonce();
                $this->UpdateNonceChanged($old_nonce);
            }
            return true;
        }
    }

    public function HasAllRequiredInfo()
    {
        $username = $this->Model->GetData(RegistrationDbTable::USERNAME);
        $password =  $this->Model->GetData(RegistrationDbTable::PASSWORD);
        $email =  $this->Model->GetData(RegistrationDbTable::EMAIL);

        return ($username and $password and $email);
    }

    public function SetState($state)
    {
        $this->Model->SetData(RegistrationDbTable::STATE, $state);
        $this->Model->SaveData();
    }

    public function GetState()
    {
        if($this->has_data)
        {
            return $this->Model->GetData(RegistrationDbTable::STATE);
        }
        else
        {
            return null;
        }
    }

    public function UserExist()
    {
        $username = $this->Model->GetData(RegistrationDbTable::USERNAME);

        $user_id = username_exists($username);
        if($user_id === false){
            return false;
        }else{
            return true;
        }
    }

    public function EmailExist()
    {
        $email = $this->Model->GetData(RegistrationDbTable::EMAIL);

        $user_id = email_exists($email);
        if($user_id === false){
            return false;
        }else{
            return true;
        }
    }

    public function CreateNewUser()
    {
        DebugLogger::WriteDebugNote();

        $result = false;

        $username = $this->Model->GetData(RegistrationDbTable::USERNAME);

        if(validate_username($username))
        {
            $email = $this->Model->GetData(RegistrationDbTable::EMAIL);

            $temp_password = wp_hash_password(Misc::RandomString(8));

            $wp_user_id = wp_create_user($username, $temp_password, $email);

            if(!is_wp_error($wp_user_id))
            {
                $password_hash = $this->Model->GetData(RegistrationDbTable::PASSWORD);
                $this->SetHashedWpPassword($password_hash, $wp_user_id);

                $this->Model->SetData(RegistrationDbTable::STATE, RegistrationDbTable::STATE_REGISTRATION_USER_CREATED);
                $this->Model->SetData(RegistrationDbTable::WP_USER_ID, $wp_user_id);
                $this->Model->SaveData();

                $user_data['ID'] = $wp_user_id;
                $user_data['first_name'] = addslashes($this->Model->GetData(RegistrationDbTable::FIRST_NAME));
                $user_data['last_name'] = addslashes($this->Model->GetData(RegistrationDbTable::LAST_NAME));

                $result = wp_update_user($user_data);
                if(is_wp_error($result))
                {
                    DebugLogger::WriteDebugError('Error updating user.');
                }

                $this->SendEmailNotificationNewUser($wp_user_id);

                $result = true;
            }
        }

        return $result;
    }

    private function SetHashedWpPassword($password_hashed, $user_id )
    {
        global $wpdb;
        $wpdb->update($wpdb->users, array('user_pass' => $password_hashed, 'user_activation_key' => ''), array('ID' => $user_id) );
        wp_cache_delete($user_id, 'users');
    }

    public function LogInRegisteredUser()
    {
        DebugLogger::WriteDebugNote();

        $username = $this->Model->GetData(RegistrationDbTable::USERNAME);

        $user = get_user_by('login', $username);
        if(!($user === false))
        {
            /* Need to catch the cookie used for authentication. The cookie will be set by wp_set_auth_cookie.
               This will not be installed until next browser request, and we may need it for nonce generation. */
            add_action('set_logged_in_cookie','ARU_ReadMoreLogin\MembershipController::InstallLoggedInCookie');

            $user_id = $user->ID;
            wp_set_current_user($user_id, $username);
            wp_set_auth_cookie($user_id);
            do_action('wp_login', $username, $user);

            /* After login the nonce is not valid any more.
               Create a new nonce and store it for nonce verification during this http request. */
            $old_nonce = $this->GetNonce();
            $this->UpdateNonceChanged($old_nonce);

            DebugLogger::WriteDebugNote('User ' . DebugLogger::Obfuscate($username) . ' logged in.');

            return true;
        }
        else
        {
            DebugLogger::WriteDebugNote('User ' . DebugLogger::Obfuscate($username) . ' failed to log in.');

            return false;
        }
    }

    static function InstallLoggedInCookie($logged_in_cookie)
    {
        $_COOKIE[LOGGED_IN_COOKIE] = $logged_in_cookie;
    }

    public function LogInUser($username, $password, $remember)
    {
        if(isset($remember) && ($remember === 1))
        {
            $remember = true;
        }
        else
        {
            $remember = false;
        }

        $credentials = array(
            'user_login' => $username,
            'user_password' => $password,
            'remember' => $remember
        );

        $user = wp_signon($credentials, false);

        if (is_wp_error($user))
        {
            return false;
        }
        else
        {
            /* Need to catch the cookie used for authentication. The cookie will be set by wp_set_auth_cookie.
               This will not be installed until next browser request, and we may need it for nonce generation. */
            add_action('set_logged_in_cookie','ARU_ReadMoreLogin\MembershipController::InstallLoggedInCookie');

            $user_id = $user->ID;
            wp_set_current_user($user_id, $username);
            wp_set_auth_cookie($user_id, $remember);
            do_action('wp_login', $username, $user);

            /* After login the nonce is not valid any more.
               Create a new nonce and store it for nonce verification during this http request. */
            $old_nonce = $this->GetNonce();
            $this->UpdateNonceChanged($old_nonce);

            return true;
        }
    }

    public function LogOutUser()
    {
        wp_logout();
        wp_set_current_user(0);

        $_COOKIE[LOGGED_IN_COOKIE] = '';

        /* After logout the nonce for this session is not valid any more. */
        $old_nonce = $this->GetNonce();
        $this->UpdateNonceChanged($old_nonce);

        return true;
    }

    public function GetRegId()
    {
        if($this->has_data)
        {
            return $this->Model->GetData(RegistrationDbTable::PRIMARY_KEY);
        }
        else
        {
            return null;
        }
    }

    public function GetRegType()
    {
        if($this->has_data)
        {
            return $this->Model->GetData(RegistrationDbTable::REG_TYPE);
        }
        else
        {
            return null;
        }
    }

    public function GetPageId()
    {
        if($this->has_data)
        {
            return $this->Model->GetData(RegistrationDbTable::POST_ID);
        }
        else
        {
            return null;
        }
    }

    public function GetNonce()
    {
        if($this->has_data)
        {
            return $this->Model->GetData(RegistrationDbTable::NONCE);
        }
        else
        {
            return null;
        }
    }

    public function GetSecret()
    {
        if($this->has_data)
        {
            return $this->Model->GetData(RegistrationDbTable::SECRET);
        }
        else
        {
            return null;
        }
    }

    public function GetUsername()
    {
        if($this->has_data)
        {
            return $this->Model->GetData(RegistrationDbTable::USERNAME);
        }
        else
        {
            return null;
        }
    }

    public function GetFirstName()
    {
        if($this->has_data)
        {
            return $this->Model->GetData(RegistrationDbTable::FIRST_NAME);
        }
        else
        {
            return null;
        }
    }

    public function GetLastName()
    {
        if($this->has_data)
        {
            return $this->Model->GetData(RegistrationDbTable::LAST_NAME);
        }
        else
        {
            return null;
        }
    }

    public function GetUsernameOrNames($username, $first_name, $last_name)
    {
        if (($first_name) == '' and ($last_name == ''))
        {
            $name = $username;
        }
        else
        {
            if($first_name == '')
            {
                $name = $last_name;
            }
            else if($last_name == '')
            {
                $name = $first_name;
            }
            else
            {
                $name = $first_name . ' ' . $last_name;
            }
        }
        return $name;
    }

    public function GetPassword()
    {
        if($this->has_data)
        {
            return $this->Model->GetData(RegistrationDbTable::PASSWORD);
        }
        else
        {
            return null;
        }
    }

    public function GetEmail()
    {
        if($this->has_data)
        {
            return $this->Model->GetData(RegistrationDbTable::EMAIL);
        }
        else
        {
            return null;
        }
    }

    public function GetWpUserId()
    {
        if($this->has_data)
        {
            return $this->Model->GetData(RegistrationDbTable::WP_USER_ID);
        }
        else
        {
            return null;
        }
    }

    public function GetCookie()
    {
        if($this->has_data)
        {
            return $this->Model->GetData(RegistrationDbTable::COOKIE);
        }
        else
        {
            return null;
        }
    }

    public function GetErrorMessage()
    {
        if($this->has_data)
        {
            if (isset($this->error_message) and ($this->error_message != ''))
            {
                return $this->error_message;
            }
        }
        return esc_html__('Undefined error.', 'read-more-login');
    }

    protected function ResponsePushData()
    {
        $data['result'] = $this->response_result;
        $data['response_read_more'] = $this->response_read_more;
        $data['form'] = $this->response_form;
        $data['message'] = $this->response_messages;

        $GLOBALS['aru_rml_response_data'] = $data;
    }

    protected function ResponsePopData()
    {
        if(isset($GLOBALS['aru_rml_response_data']))
        {
            $data = $GLOBALS['aru_rml_response_data'];

            $this->response_result = $data['result'];
            $this->response_read_more = $data['response_read_more'];
            $this->response_form = $data['form'];
            $this->response_messages = $data['message'];

            return true;
        }
        else
        {
            return false;
        }
    }

    protected function ResponseSetOk($msg)
    {
        $this->SetServerContextData('response_status', 'ok');
        $this->SetServerContextData('response_color', 'green');
        $this->SetServerContextData('response_message', $msg);
    }

    protected function ResponseSetReadMore()
    {
        $this->response_result = self::REG_RESP_RESULT_OK;
        $this->response_read_more = true;
    }

    protected function ResponseSetError($msg)
    {
        $this->SetServerContextData('response_status', 'error');
        $this->SetServerContextData('response_color', 'red');
        $this->SetServerContextData('response_message', $msg);
    }

    protected function ResponseSetInputError($input_name)
    {
        $error_input = $this->GetServerContextData('error_input', array());
        $error_input[$input_name] = true;
        $this->SetServerContextData('error_input', $error_input);
    }

    protected function HideInputErrorIndications()
    {
        $this->View->HideInputErrorIndications();
    }

    protected function ShowInputErrorIndications()
    {
        $error_inputs = $this->GetServerContextData('error_input');
        if($error_inputs)
        {
            $this->View->ShowInputErrorIndications($error_inputs);
        }
    }

    protected function ResponseSetLoggedIn()
    {
        $this->SetServerContextData('have_logged_in', 1);
    }

    protected function LoadModelValues($values = array())
    {
        /* Model values only loaded by this class itself. Override this function to prevent base class from loading
           model values.*/
        return $values;
    }

    protected function InitView($values)
    {
        parent::InitView($values);

        $this->AddHiddenFields();
    }

    protected function ReadTermsFromView()
    {
        $terms_accepted = 1;
        $design_options = get_option(SettingsFormOptions::OPTION_NAME);
        if($design_options[SettingsFormOptions::REGISTER_MUST_ACCEPT_TERMS])
        {
            $terms_accepted = $this->View->AcceptTerms->GetValue();
        }
        return $terms_accepted;
    }
}
