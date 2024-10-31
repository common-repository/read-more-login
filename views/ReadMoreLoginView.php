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

use WP_PluginFramework\HtmlComponents\TextLine;
use WP_PluginFramework\HtmlComponents\PushButton;
use WP_PluginFramework\HtmlComponents\Link;
use WP_PluginFramework\HtmlComponents\StatusBar;
use WP_PluginFramework\HtmlElements\H;
use WP_PluginFramework\HtmlElements\Div;

class ReadMoreLoginView extends ReadMoreView
{
    /** @var StatusBar */
    public $StatusBarHeader;
    /** @var TextLine */
    public $Username;
    /** @var TextLine */
    public $Password;
    /** @var PushButton */
    public $ButtonLogin;
    /** @var Link */
    public $Link1;
    /** @var StatusBar */
    public $StatusBarFooter;

    public function __construct($id, $controller)
    {
        parent::__construct($id, $controller);

        $design_options = get_option(SettingsFormOptions::OPTION_NAME);

        $this->ContentConfig['form_input_encapsulation'] = 'table';

        $header = new H(2);
        $this->AddHeader('Header', $header);

        $div = new Div();
        $this->AddHeader('Message', $div);

        if($design_options[SettingsFormOptions::LOGIN_SHOW_LOGIN_FORM])
        {
            $this->AddHeader('StatusBarHeader', new StatusBar());

            $this->AddFormInput('Username', new TextLine(esc_html__('Username:', 'read-more-login'), null, 'username'));
            $this->AddFormInput('Password', new TextLine(esc_html__('Password:', 'read-more-login'), null, 'password', ['type' => 'password']));

            /* translators: Button label. */
            $this->AddButton('ButtonLogin', new PushButton(esc_html__('Log in', 'read-more-login')));

            if($design_options[SettingsFormOptions::LOGIN_SHOW_REGISTER_BTN])
            {
                if($design_options[SettingsFormOptions::LOGIN_SHOW_REGISTER_AS_LINK])
                {
                    $linking_options = new SettingsLinkingOptions();
                    $register_url = $linking_options->GetRegisterUrl();
                    /* translators: Link label, start register as new user. */
                    $this->AddFooter('ButtonStartRegister', new Link($register_url, esc_html__('Register', 'read-more-login')));
                }
                else
                {
                    /* translators: Button label, start register as new user. */
                    $this->AddButton('ButtonStartRegister', new PushButton(esc_html__('Register', 'read-more-login')));
                }
            }

            if($design_options[SettingsFormOptions::LOGIN_SHOW_FORGOTTEN_LINK])
            {
                $linking_options = new SettingsLinkingOptions();
                $lost_password_url = $linking_options->GetLostPasswordUrl();
                $this->AddFooter('Link1', new Link($lost_password_url, esc_html__('Forgotten username or password?', 'read-more-login')));
            }

            $this->AddFooter('StatusBarFooter', new StatusBar());

            $this->AddHiddenFields('post_id', get_the_ID());

            $this->RegisterCallBack('LoadRemainingText');
        }
        else
        {
            $this->ShowForm = false;
        }
    }
}
