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

use WP_PluginFramework\PluginContainer;
use WP_PluginFramework\HtmlComponents\StatusBar;
use WP_PluginFramework\HtmlElements\Strong;
use WP_PluginFramework\Utils\SecurityFilter;
use WP_PluginFramework\Utils\DebugLogger;

class ReadMoreLoginController extends MembershipController
{
    /** @var RegistrationDbTable */
    protected $Model = null;

    /** @var ReadMoreLoginView | ReadMoreRegistrationView*/
    public $View;

    private $insert_data_if_secret_ok = false;

    public function __construct($test_mode = null, $view = 'ARU_ReadMoreLogin\ReadMoreLoginView')
    {
        $secret = null;
        $secret_required=false;

        if (isset($test_mode))
        {
            /* Used to preview controller in admin panel */
            $this->TestMode = true;
        }

        $action = SecurityFilter::SafeReadPostRequest('action', SecurityFilter::STRING_KEY_NAME);
        if ($action == ReadMoreLoginPlugin::WP_PLUGIN_FRAMEWORK_AJAX_HANDLER)
        {
            $event = SecurityFilter::SafeReadPostRequest('_event', SecurityFilter::STRING_KEY_NAME);
            $reg_id = SecurityFilter::SafeReadPostRequest(self::REG_ID, SecurityFilter::POSITIVE_INTEGER_ZERO);
            $nonce = SecurityFilter::SafeReadPostRequest(self::REG_NONCE, SecurityFilter::ALPHA_NUM);
            $post_id = SecurityFilter::SafeReadPostRequest(self::REG_POST_ID, SecurityFilter::POSITIVE_INTEGER);
        }
        else
        {
            $event = null;
            $reg_id = null;
            $nonce = null;

            /* The link code can contain alphanumeric and hyphen and underscore characters. */
            $rml = SecurityFilter::SafeReadGetRequest('rml', SecurityFilter::STRING_KEY_NAME);

            if (isset($rml))
            {
                /* Don't flood the debug log due to hacking attempt. Write to buffer until nonce confirmed. */
                DebugLogger::PauseWpDebugLogging();

                $data = $this->DecodeEmailUrlData($rml);

                if (isset($data))
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

        $reg_type = RegistrationDbTable::REG_TYPE_READ_MORE_REGISTRATION;

        parent::__construct($view, $event, $reg_id, $reg_type, $post_id, $nonce, $secret, $secret_required);

        if ($this->HasUserData())
        {
            $this->RegisterEvent('InitLogin', 'init', 'post');
        }

        $this->SetInitCallback('InitLogin');

        /* TODO Remove this line. This is a temporary fix to get rid of an debug error message. When reloading remaining
           text after read-more-login, the client side wants to call InitLogin. InitLogin should only be called in the
           first post when logging in. Next step when loading remaining text, InitLogin should not be called. Need to
           change JavaScript callback to not read InitLogin from hidden field. */
        $this->RegisterEvent('InitLogin', 'init', 'post');
    }

    protected function ReadNonceProtectedData($event_source)
    {
        $nonceProtectedData = parent::ReadNonceProtectedData($event_source);

        if(!$nonceProtectedData)
        {
            /* Get url contains no nonce protected data. Must recreate it before executing events. The events check for nonce.  */
            if ($this->insert_data_if_secret_ok)
            {
                if ($this->HasUserData())
                {
                    $nonce_protected_data = $this->GetNonceProtectedData();
                    $nonceProtectedData = $this->CalculateWpNonce($nonce_protected_data);

                    $this->RegisterEvent('ConfirmEmail', 'get', 'get');
                    $this->SetEvent('ConfirmEmail');
                }
            }
        }

        return $nonceProtectedData;
    }

    public function InitLogin_init($event)
    {
        /* This function is called several time during read-more-login. This is due to several ajax call which will
           each call init. Must only log-in first time. Following init calls should not generate a wrong status message. */
        if ($event === 'ButtonLogin_click')
        {
            if (!is_user_logged_in())
            {
                $username = $this->View->Username->GetText();
                $password = $this->View->Password->GetText();
                $remember = 0;

                if ($this->CheckLoginData($username, $password))
                {
                    if (!$this->LogInUser($username, $password, $remember))
                    {
                        $this->ResponseSetError(esc_html__('Wrong username or password.', 'read-more-login'));
                    }
                }
            }
        }

        if ($event === 'ConfirmEmail_get')
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
                switch ($this->GetState())
                {
                    case RegistrationDbTable::STATE_REGISTRATION_EMAIL_SENT:
                        if ($this->HasAllRequiredInfo())
                        {
                            if (!$this->UserExist())
                            {
                                if (!$this->EmailExist())
                                {
                                    if ($this->CreateNewUser())
                                    {
                                        $post_id = $this->GetPageId();
                                        Statistics::StatisticsCompleted($post_id);

                                        $this->LogInRegisteredUser();
                                        return;
                                    }
                                }
                            }
                        }

                        /* Username or e-mail already exist, or missing some other information. */
                        $this->SetState(RegistrationDbTable::STATE_REGISTRATION_MORE_INFO);
                        break;

                    case RegistrationDbTable::STATE_REGISTRATION_MORE_INFO:
                        break;

                    case RegistrationDbTable::STATE_REGISTRATION_USER_CREATED:
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

        if (is_user_logged_in())
        {
            if ($this->GetClientContextData('TestMode'))
            {
                $username = $this->View->Username->GetText();
                $password = $this->View->Password->GetText();

                if ($this->CheckLoginData($username, $password))
                {
                    $wp_user = get_user_by('login', $username);
                    if ($wp_user && wp_check_password($password, $wp_user->data->user_pass, $wp_user->ID))
                    {
                        $this->View->StatusBarFooter->SetStatusText(esc_html__('Loading text...', 'read-more-login'), StatusBar::STATUS_SUCCESS);

                        $this->RegisterCallback('LoadRemainingText');
                    }
                    else
                    {
                        $this->View->StatusBarFooter->SetStatusText(esc_html__('Wrong username or password.', 'read-more-login'), StatusBar::STATUS_ERROR);
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
                /* When user has clicked log in button, user are already logged in by the init function. */

                $this->View->StatusBarFooter->SetStatusText(esc_html__('Loading text...', 'read-more-login'), StatusBar::STATUS_SUCCESS);

                $style_options = get_option(SettingsStyleOptions::OPTION_NAME);
                if($style_options[SettingsStyleOptions::TEXT_LOADING] === SettingsStyleOptions::TEXT_LOADING_RELOAD_PAGE )
                {
                    $this->View->UpdateClientReloadPage();
                }
                else
                {
                    $this->RegisterCallback('LoadRemainingText');
                    $this->UpdateCompatWidget();
                }
            }
        }
        else
        {
            $message = $this->GetServerContextData('response_message');
            $this->View->StatusBarFooter->SetStatusText($message, StatusBar::STATUS_ERROR);
        }

        $this->ShowInputErrorIndications();
    }

    public function ButtonStartRegister_click()
    {
        $this->HideInputErrorIndications();

        if (!is_user_logged_in())
        {
            switch ($this->GetState())
            {
                case RegistrationDbTable::STATE_REGISTRATION_NOT_SET:
                case RegistrationDbTable::STATE_REGISTRATION_MORE_INFO:
                    $username = $this->View->Username->GetText();
                    $password = $this->View->Password->GetText();
                    if ($this->CheckRegisterUserData($username, $password))
                    {
                        if ($this->RegisterUsernamePassword($username, $password))
                        {
                            $this->SetState(RegistrationDbTable::STATE_REGISTRATION_STARTED);

                            $this->ReloadView('ARU_ReadMoreLogin\ReadMoreRegistrationView');

                            $post_id = $this->GetPageId();
                            Statistics::StatisticsRegister($post_id);
                        }
                    }
                    else
                    {
                        $message = $this->GetServerContextData('response_message');
                        $this->View->StatusBarFooter->SetStatusText($message, StatusBar::STATUS_ERROR);
                    }
                    break;

                case RegistrationDbTable::STATE_REGISTRATION_USER_CREATED:
                    $username = $this->View->Username->GetText();
                    $password = $this->View->Password->GetText();
                    if ($this->CheckRegisterUserData($username, $password))
                    {
                        $page_id = $this->GetPageId();
                        if($this->CreateNewRegistrationRecord(RegistrationDbTable::REG_TYPE_READ_MORE_REGISTRATION, $page_id))
                        {
                            if ($this->RegisterUsernamePassword($username, $password))
                            {
                                $this->SetState(RegistrationDbTable::STATE_REGISTRATION_STARTED);

                                $this->ReloadView('ARU_ReadMoreLogin\ReadMoreRegistrationView');

                                $post_id = $this->GetPageId();
                                Statistics::StatisticsRegister($post_id);
                            }
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
        else if ($this->GetClientContextData('TestMode'))
        {
            $username = $this->View->Username->GetText();
            $password = $this->View->Password->GetText();
            if ($this->CheckRegisterUserData($username, $password))
            {
                $preview_data = new AdminPreviewData();
                $preview_data->LoadData();
                $preview_data->SetData(AdminPreviewData::USERNAME, $username);
                $preview_data->SetData(AdminPreviewData::EMAILS, '');
                $preview_data->SetData(AdminPreviewData::FIRST_NAME, '');
                $preview_data->SetData(AdminPreviewData::LAST_NAME, '');
                $preview_data->SaveData();

                $this->ReloadView('ARU_ReadMoreLogin\ReadMoreRegistrationView');
            }
            else
            {
                $message = $this->GetServerContextData('response_message');
                $this->View->StatusBarFooter->SetStatusText($message, StatusBar::STATUS_ERROR);
            }
        }

        $this->ShowInputErrorIndications();
    }

    public function ButtonRegisterEmail_click()
    {
        $this->HideInputErrorIndications();

        if (!is_user_logged_in())
        {
            switch ($this->GetState())
            {
                case RegistrationDbTable::STATE_REGISTRATION_STARTED:
                    $email = $this->View->Email->GetText();
                    $terms_accepted = $this->ReadTermsFromView();
                    $email = trim($email);

                    if ($this->CheckRegistrationEmailData($email))
                    {
                        if ($this->CheckAndSaveName())
                        {
                            if ($this->CheckRegistrationTermsAccepted($terms_accepted))
                            {
                                if ($this->RegisterEmail($email))
                                {
                                    $this->SetState(RegistrationDbTable::STATE_REGISTRATION_EMAIL_SENT);

                                    $this->ReloadView('ARU_ReadMoreLogin\ReadMoreCheckEmailView');

                                    $post_id = $this->GetPageId();
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
                    }
                    else
                    {
                        $message = $this->GetServerContextData('response_message');
                        $this->View->StatusBarFooter->SetStatusText($message, StatusBar::STATUS_ERROR);
                    }
                    break;

                case RegistrationDbTable::STATE_REGISTRATION_MORE_INFO:
                    $email = $this->View->Email->GetText();
                    $email = trim($email);

                    if ($this->CheckRegistrationEmailData($email))
                    {
                        if ($this->CheckAndSaveName())
                        {
                            if ($this->RegisterEmail($email))
                            {
                                $this->SetState(RegistrationDbTable::STATE_REGISTRATION_EMAIL_SENT);

                                $this->ReloadView('ARU_ReadMoreLogin\ReadMoreCheckEmailView');

                                $post_id = $this->GetPageId();
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
                    break;

                default:
                    DebugLogger::WriteDebugError('Unhandled state ' . $this->GetState());
            }
        }
        else if ($this->GetClientContextData('TestMode'))
        {
            $first_name = '';
            $last_name = '';

            $email = $this->View->Email->GetText();
            $email = trim($email);

            $design_options = get_option(SettingsFormOptions::OPTION_NAME);

            if($design_options[SettingsFormOptions::REGISTER_COLLECT_FIRST_NAME])
            {
                $first_name = $this->View->FirstName->GetText();
            }

            if($design_options[SettingsFormOptions::REGISTER_COLLECT_LAST_NAME])
            {
                $last_name = $this->View->LastName->GetText();
            }

            if ($this->CheckRegistrationEmailData($email))
            {
                $preview_data = new AdminPreviewData();
                $preview_data->LoadData();
                $preview_data->SetData(AdminPreviewData::EMAILS, $email);
                $preview_data->SetData(AdminPreviewData::FIRST_NAME, $first_name);
                $preview_data->SetData(AdminPreviewData::LAST_NAME, $last_name);
                $preview_data->SaveData();

                $this->ReloadView('ARU_ReadMoreLogin\ReadMoreCheckEmailView');
            }
            else
            {
                $message = $this->GetServerContextData('response_message');
                $this->View->StatusBarFooter->SetStatusText($message, StatusBar::STATUS_ERROR);
            }
        }

        $this->ShowInputErrorIndications();
    }

    public function ConfirmEmail_get()
    {
        if (!is_user_logged_in())
        {
            switch ($this->GetState())
            {
                case RegistrationDbTable::STATE_REGISTRATION_MORE_INFO:
                    $this->CheckAllRegistrationInfoGood();
                    break;

                case RegistrationDbTable::STATE_REGISTRATION_USER_CREATED:
                    $this->View->StatusBarHeader->SetStatusText(esc_html__('This link has been used and registration is completed. You can now log in.', 'read-more-login'), StatusBar::STATUS_SUCCESS);
                    break;

                default:
                    DebugLogger::WriteDebugError('Unhandled state ' . $this->GetState());
            }
        }
    }

    public function ButtonRegisterUsername_click()
    {
        $this->HideInputErrorIndications();

        if (!is_user_logged_in())
        {
            switch ($this->GetState())
            {
                case RegistrationDbTable::STATE_REGISTRATION_MORE_INFO:
                    $username = $this->View->Username->GetText();
                    $this->Model->SetData(RegistrationDbTable::USERNAME, $username);
                    $this->Model->SaveData();

                    if ($this->CheckAllRegistrationInfoGood())
                    {
                        if ($this->CreateNewUser())
                        {
                            $post_id = $this->GetPageId();
                            $stat = new Statistics();
                            $stat->StatisticsCompleted($post_id);

                            if ($this->LogInRegisteredUser())
                            {
                                $this->View->StatusBarFooter->SetStatusText(esc_html__('Username accepted. Loading text...', 'read-more-login'), StatusBar::STATUS_SUCCESS);
                                $this->RegisterCallback('LoadRemainingText');

                                $this->UpdateCompatWidget();
                            }
                            else
                            {
                                $this->ResponseSetError(esc_html__('Wrong username or password.', 'read-more-login'));
                            }
                        }
                    }
                    break;

                default:
                    DebugLogger::WriteDebugError('Unhandled state ' . $this->GetState());
            }
        }

        $this->ShowInputErrorIndications();
    }

    public function LoadRemainingText_callback()
    {
        if ($this->GetClientContextData('TestMode'))
        {
            $test_text = '<p>Nunc et purus nibh. Proin malesuada commodo congue. Nulla faucibus metus vel ex feugiat interdum vel sit amet metus. Fusce enim sapien, placerat id gravida in, volutpat sit amet tellus. Duis ornare erat nibh, ac molestie odio accumsan ac. Vivamus fermentum magna nibh, consequat tincidunt enim sodales eget. Sed at erat id sapien ullamcorper blandit. Sed rutrum elit tortor, sed iaculis tortor interdum vel. Nam vel augue aliquet, gravida purus sed, lobortis mi. Aliquam rhoncus felis eu lectus viverra vulputate. Donec faucibus tortor eu elit feugiat pretium. Sed placerat sem sagittis metus commodo, sed congue sapien condimentum. Praesent vehicula, est a suscipit sagittis, risus mi sollicitudin lectus, eget dignissim lectus urna a elit. Nunc sagittis consectetur facilisis. Morbi quam risus, porttitor non porttitor et, molestie vitae velit.</p>';
            $test_text .= '<p>Praesent ut enim nisl. Aliquam quis porta dui. Aliquam aliquet aliquet dolor imperdiet hendrerit. Donec in tincidunt magna, eu varius nisl. Fusce sodales odio eget risus sollicitudin tempor. Sed laoreet odio fringilla, congue turpis sit amet, fringilla tortor. Sed efficitur bibendum erat sit amet imperdiet. Integer eleifend pretium leo vitae consectetur.</p>';
            $test_text .= '<p>Aliquam ut gravida orci. Nullam laoreet gravida feugiat. Pellentesque sed mi congue, luctus ex quis, tincidunt erat. Sed ut neque id nisl ullamcorper volutpat. Phasellus quis dolor ut arcu mollis faucibus non in arcu. Nam faucibus eu est ac dignissim. Integer suscipit at enim vel imperdiet. Curabitur commodo interdum sapien, malesuada scelerisque libero ultricies sed. Aliquam erat volutpat.</p>';
            $test_text .= '<p>Aliquam in pharetra neque. Etiam sollicitudin pulvinar ligula, quis cursus eros rutrum vel. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Donec ac porttitor lorem, nec porta justo. Maecenas sagittis urna non nisi lobortis convallis a vitae lacus. Donec sed sem in ipsum egestas malesuada laoreet nec est. Morbi blandit metus est, vel ornare lectus elementum quis.</p>';
            $test_text .= '<p>Etiam eget leo molestie, porta diam in, ullamcorper tellus. Nullam quis dapibus odio. Donec nibh ante, pretium a odio eget, molestie hendrerit elit. Vivamus ac molestie neque. Integer at nisl eget magna viverra pulvinar. Etiam commodo, lectus viverra tristique ultricies, eros ex convallis mauris, aliquet efficitur turpis lacus at mauris. In egestas augue a eros congue scelerisque. Ut condimentum, lacus ac placerat fringilla, dui ex tempor nisi, vitae volutpat lacus arcu a lorem. Morbi bibendum nisl vel justo luctus mattis.</p>';
            $test_text .= '<p>Aliquam leo nulla, cursus in pellentesque a, rutrum quis eros. Integer sodales quam nec sollicitudin porttitor. Nunc orci felis, pulvinar aliquam imperdiet ac, condimentum id enim. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Vestibulum leo dui, pharetra non nisi sit amet, tincidunt ullamcorper nibh. Vivamus viverra, massa in pharetra sodales, ante nibh efficitur neque, nec eleifend sem nisi eu erat. Suspendisse nunc ipsum, imperdiet et lobortis eget, tristique id odio.</p>';
            $test_text .= '<p>Proin elementum ultricies finibus. Sed orci neque, posuere at tristique vitae, molestie sed nulla. Fusce dignissim tempor ipsum vitae posuere. Quisque ultrices vestibulum facilisis. Sed ornare augue risus, eu dignissim ante iaculis eget. Nulla blandit, velit non aliquet blandit, augue massa posuere ex, lacinia aliquet turpis velit eget ipsum. Morbi vel gravida nunc. Mauris consectetur sem sed magna mollis, ac sagittis risus auctor. Cras suscipit quam sed justo sollicitudin tristique quis in lectus. Morbi nec egestas dolor.</p>';
            $test_text .= '<p>Vivamus mattis volutpat tincidunt. Donec sodales massa eget erat aliquam, eget consectetur dolor posuere. Duis ultrices mauris et sagittis placerat. Donec viverra varius metus, id placerat elit finibus a. Mauris velit lectus, lobortis elementum ante sit amet, fringilla convallis ex. Mauris facilisis porta porta. Phasellus nec leo et erat vulputate eleifend.</p>';
            $test_text .= '<p>Suspendisse dapibus, leo at fringilla vehicula, lorem tellus aliquam leo, at posuere sem nulla non mauris. Proin ultricies metus eu velit sodales, vel lobortis orci rhoncus. Praesent sagittis ipsum dolor, vitae pharetra quam tempus eu. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris venenatis feugiat augue a semper. Vivamus libero orci, lobortis varius massa a, volutpat egestas ex. Praesent ac finibus tortor. Proin gravida euismod lectus, non vehicula velit auctor eu. Aliquam erat volutpat. Donec vel auctor nisi. Sed ex tellus, condimentum nec mauris at, suscipit commodo ipsum. Etiam eu scelerisque massa. Nunc vitae blandit arcu, at sagittis ipsum. Cras ut imperdiet ante, ac mollis arcu. Praesent tincidunt tortor vulputate dolor varius, id volutpat quam suscipit.</p>';

            $this->View->UpdateClientLoadText(
                '#' . $this->Id,
                'div#rml_fade_content',
                'div#aru_remaining_content',
                $test_text
            );
        }
        else
        {
            if (is_user_logged_in())
            {
                /* Need template settings to filter certain post contents, for example the youtube links. */
                //do_action( 'after_setup_theme' );
                do_action( 'template_redirect' );

                /* If user has been logged in, the post_id from user has already been verified against nonce */
                $post_id = SecurityFilter::SafeReadPostRequest(self::REG_POST_ID, SecurityFilter::POSITIVE_INTEGER);

                DebugLogger::WriteDebugNote('Load remaining protected content post_id=' . strval($post_id));

                /* For autoembed in filter to work, the post object must be loaded into the GLOBALS */
                $GLOBALS['post'] = get_post($post_id);

                $content = $GLOBALS['post']->post_content;

                $GLOBALS['rml_read_more_loading_remaining_text'] = true;

                /* Want to do filtering first of the complete text to get it undisturbed, later we pick out whats will be uploaded. */
                $content = apply_filters('the_content', $content);

                /* The following code line was taken from WordPress the_content function. It is executed when a post
                   is displayed. Assume same string operation must be executed when loading content from AJAX call. */
                $content = str_replace(']]>', ']]&gt;', $content);

                $position = strpos($content, ReadMoreLoginPlugin::ARU_READMORELOGIN_SHORT_CODE);
                if ($position > 0) {
                    $position          = strpos( $content, ReadMoreLoginPlugin::ARU_READMORELOGIN_SHORT_CODE ) + strlen( ReadMoreLoginPlugin::ARU_READMORELOGIN_SHORT_CODE );
                    $content_remaining = substr( $content, $position );
                    $content_remaining = str_replace( "\n<!-- /wp:shortcode -->\n", '', $content_remaining );

                    $remaining_length = strpos( $content_remaining, ReadMoreLoginPlugin::ARU_READMORELOGIN_SHORT_CODE_END );
                    if ( $remaining_length ) {
                        $content_remaining = substr( $content_remaining, 0, $remaining_length );
                        $content_remaining = str_replace( "\n<!-- wp:shortcode -->\n", '', $content_remaining );
                    }
                } else {
                    $content_remaining= $GLOBALS['rml_read_more_login_remaining_text'];
                }

                $this->View->UpdateClientLoadText(
                    'div#rml_readmorelogin_placeholder',
                    'div#rml_fade_content',
                    'div#aru_remaining_content',
                    $content_remaining
                );
            }
            else
            {
                $this->View->StatusBarFooter->SetStatusText(esc_html__('Error. Can not log in.', 'read-more-login'), StatusBar::STATUS_ERROR);
            }
        }
    }

    public function CheckAllRegistrationInfoGood()
    {
        $username = $this->GetUsername();
        $email = $this->GetEmail();

        if (isset($username))
        {
            if (isset($email))
            {
                if (!$this->EmailExist())
                {
                    if (validate_username($username))
                    {
                        if (!$this->UserExist())
                        {
                            return true;
                        }
                        else
                        {
                            $this->ReloadView('ARU_ReadMoreLogin\ReadMoreRegisterUsernameView');
                            $s = new Strong($username);
                            $formatted_username = $s->DrawHtml();
                            /* translators: %s: Username. */
                            $msg = sprintf(esc_html__('Username %s already taken. Please select another username.', 'read-more-login'), $formatted_username);
                            $this->View->StatusBarHeader->SetStatusHtml($msg,  StatusBar::STATUS_ERROR);
                        }
                    }
                    else
                    {
                        $this->ReloadView('ARU_ReadMoreLogin\ReadMoreRegisterUsernameView');
                        $s = new Strong($username);
                        $formatted_username = $s->DrawHtml();
                        /* translators: %s: Username. */
                        $msg = sprintf(esc_html__('Error. Invalid username %s. Please select another username.', 'read-more-login'), $formatted_username);
                        $this->View->StatusBarHeader->SetStatusHtml($msg,  StatusBar::STATUS_ERROR);
                    }
                }
                else
                {
                    $this->ReloadView('ARU_ReadMoreLogin\ReadMoreLoginView');
                    $s = new Strong($email);
                    $formatted_email = $s->DrawHtml();
                    /* translators: %s: E-mail address. */
                    $msg = sprintf(esc_html__('User account with e-mail %s already exists. You can now log in.', 'read-more-login'), $formatted_email);
                    $this->View->StatusBarHeader->SetStatusHtml($msg, StatusBar::STATUS_ERROR);
                }
            }
            else
            {
                $this->ReloadView('ARU_ReadMoreLogin\ReadMoreRegistrationView');
                $msg = esc_html__('E-mail address registration error. Please register your e-mail address again.', 'read-more-login');
                $this->View->StatusBarHeader->SetStatusText($msg , StatusBar::STATUS_ERROR);
            }
        }
        else
        {
            $this->ReloadView('ARU_ReadMoreLogin\ReadMoreRegisterUsernameView');
            $msg = esc_html__('Error creating username. Please select another username.', 'read-more-login');
            $this->View->StatusBarHeader->SetStatusText($msg, StatusBar::STATUS_ERROR);
        }

        return false;
    }

    public function UpdateCompatWidget()
    {
        /* Update the compact widget with new login status. */
        /* Select login username or full name if it exists. */
        $wp_user_id = get_current_user_id();
        $user_info = get_userdata($wp_user_id);
        $username = $user_info->user_login;
        $first_name = $user_info->first_name;
        $last_name = $user_info->last_name;

        $display_name = $this->GetUsernameOrNames($username, $first_name, $last_name);

        $this->View->UpdateClientDom('div.aru_rml_logout_widget', 'css', array('display', 'none'));
        $this->View->UpdateClientDom('div.aru_rml_login_widget', 'css', array('display', 'block'));
        $this->View->UpdateClientDom('div.aru_rml_login_widget span', 'text', $display_name);

        /* If logged in status has changed, we need to update logout url on page because nonce has changed during log in. */
        $logout_url = wp_logout_url();
        $logout_url = str_replace('&amp;', '&', $logout_url);
        $this->View->UpdateClientDom('a.aru_rml_logout_href', 'attr', array('href', $logout_url));
    }

    function GetFreeContentHtmlPart($content)
    {
        $free_content = '';

        $post_id = get_the_ID();
        if ($post_id)
        {
            Statistics::StatisticsPageView($post_id);
        }

        $free_content .= $content;

        return $free_content;
    }

    public function GetFadeCover()
    {
        $style_div = '';
        $design_options = get_option(SettingsStyleOptions::OPTION_NAME);
        if ( isset( $design_options[ SettingsStyleOptions::FADING_ENABLE ] ) and $design_options[ SettingsStyleOptions::FADING_ENABLE ] == '1' )
        {
            $style = 'position: absolute;
top:-' . $design_options[SettingsStyleOptions::FADING_HEIGHT] . 'em;
width:100%;
height:' . $design_options[SettingsStyleOptions::FADING_HEIGHT] . 'em;
background: -webkit-linear-gradient(rgba(255, 255, 255, 0) 0%,' . $design_options[SettingsStyleOptions::FADE_BACKGROUND_COLOR] . ' 100%);
background-image: -moz-linear-gradient(rgba(255, 255, 255, 0) 0%,' . $design_options[SettingsStyleOptions::FADE_BACKGROUND_COLOR] . ' 100%);
background-image: -o-linear-gradient(rgba(255, 255, 255, 0) 0%,' . $design_options[SettingsStyleOptions::FADE_BACKGROUND_COLOR] . ' 100%);
background-image: linear-gradient(rgba(255, 255, 255, 0) 0%,' . $design_options[SettingsStyleOptions::FADE_BACKGROUND_COLOR] . ' 100%);
background-image: -ms-linear-gradient(rgba(255, 255, 255, 0) 0%,' . $design_options[SettingsStyleOptions::FADE_BACKGROUND_COLOR] . ' 100%);';

            $style_div = '<div id="rml_fade_content" style="' . $style . '"></div>';
        }
        return $style_div;
    }

    function GetProtectedContentPlaceholder()
    {
        $protected_content = '<div id=aru_remaining_content></div>';
        return $protected_content;
    }

    protected function EnqueueScript()
    {
        parent::EnqueueScript();

        $plugin_version = PluginContainer::GetPluginVersion();

        $script_handler = 'aru_readmorelogin_plugin_script_handler';
        $src = plugins_url() . '/read-more-login/js/rml.js';
        wp_enqueue_script($script_handler, $src, array('jquery'), $plugin_version, true);

        $style_options = get_option(SettingsStyleOptions::OPTION_NAME);

        if($style_options[SettingsStyleOptions::OVERRIDE_CSS] == SettingsStyleOptions::CSS_USE_PLUGIN_CSS)
        {
            $style_handler = 'aru_readmorelogin_plugin_style_handler';
            $style_url = plugins_url() . '/read-more-login/css/rml_style.css';
            wp_enqueue_style($style_handler, $style_url, array(), $plugin_version);
        }
        else if($style_options[SettingsStyleOptions::OVERRIDE_CSS] == SettingsStyleOptions::CSS_USE_CUSTOM_CSS)
        {
            $style_handler = 'aru_readmorelogin_plugin_style_handler';
            $style_url = plugins_url() . '/read-more-login/css/rml_custom_style.css';
            wp_enqueue_style($style_handler, $style_url, array(), $plugin_version);
        }
    }

    protected function LoadContextData()
    {
        parent::LoadContextData();

        if ($this->TestMode)
        {
            $this->SetClientContextData('TestMode', 1);
        }
    }

    protected function DrawView($parameters=null)
    {
        $make_form_parameters = false;
        $view = $this->GetViewClass();
        $header_text = '';
        $message_text = '';

        switch($view)
        {
            case 'ARU_ReadMoreLogin\ReadMoreLoginView';
                $design_options = get_option(SettingsFormOptions::OPTION_NAME);
                $header_text = $design_options[SettingsFormOptions::LOGIN_HEADER_TEXT];
                $message_text = $design_options[SettingsFormOptions::LOGIN_MESSAGE_TEXT];
                $make_form_parameters = true;
                break;

            case 'ARU_ReadMoreLogin\ReadMoreRegistrationView';
                $design_options = get_option(SettingsFormOptions::OPTION_NAME);
                $header_text = $design_options[SettingsFormOptions::REGISTER_HEADER_TEXT];
                $message_text = $design_options[SettingsFormOptions::REGISTER_MESSAGE_TEXT];
                $make_form_parameters = true;
                break;

            case 'ARU_ReadMoreLogin\ReadMoreCheckEmailView':
                $design_options = get_option(SettingsFormOptions::OPTION_NAME);
                $header_text = $design_options[SettingsFormOptions::CHECK_EMAIL_HEADER_TEXT];
                $message_text = $design_options[SettingsFormOptions::CHECK_EMAIL_MESSAGE_TEXT];
                $make_form_parameters = true;
                break;
        }

        if($make_form_parameters)
        {
            $variables = $this->GetEmailKeyList('N/A');

            if($this->GetClientContextData('TestMode'))
            {
                $preview_data = new AdminPreviewData();
                $preview_data->LoadData();

                $username = $preview_data->GetData(AdminPreviewData::USERNAME);
                $first_name = $preview_data->GetData(AdminPreviewData::FIRST_NAME);
                $last_name = $preview_data->GetData(AdminPreviewData::LAST_NAME);
                $email = $preview_data->GetData(AdminPreviewData::EMAILS);

                if(!$username)
                {
                    $current_user = wp_get_current_user();
                    $username = $current_user->user_login;
                }

                if(!$email)
                {
                    $current_user = wp_get_current_user();
                    $email = $current_user->user_email;
                }

                $variables['name'] = $this->GetUsernameOrNames($username, $first_name, $last_name);
                $variables['username'] = $username;
                $variables['first_name'] = $first_name;
                $variables['last_name'] = $last_name;
                $variables['email'] = $email;
                $variables['title'] = 'Lorem Ipsum';
                $variables['site_name'] = get_bloginfo('name');
                $variables['site_url'] = site_url();
            }
            else
            {
                $post_id = $this->GetPageId();
                $variables = $this->PrepareVariables(null, $post_id, true);
            }

            $parameters['header_text'] = $this->ReplaceEmailVariables($header_text, $variables);
            $parameters['message_text'] = $this->ReplaceEmailVariables($message_text, $variables);
        }

        return parent::DrawView($parameters);
    }
}
