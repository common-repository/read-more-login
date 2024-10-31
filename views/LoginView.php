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

use WP_PluginFramework\HtmlComponents\CheckBox;
use WP_PluginFramework\HtmlComponents\TextLine;
use WP_PluginFramework\HtmlComponents\PushButton;
use WP_PluginFramework\HtmlComponents\Link;
use WP_PluginFramework\HtmlComponents\StatusBar;

class LoginView extends FrontPageView
{
    /** @var StatusBar */
    public $StatusBarHeader;
    /** @var TextLine */
    public $Username;
    /** @var TextLine */
    public $Password;
    /** @var CheckBox */
    public $Remember;
    /** @var PushButton */
    public $ButtonLogin;
    /** @var Link */
    public $Link2;
    /** @var Link */
    public $Link1;
    /** @var StatusBar */
    public $StatusBarFooter;

    public function __construct($id, $controller)
    {
        parent::__construct($id, $controller);

        $this->SetMethod(self::SEND_METHOD_POST);

        $this->AddHeader('StatusBarHeader', new StatusBar());

        $this->AddFormInput('Username', new TextLine(esc_html__('Username:', 'read-more-login'), '', 'username'));
        $this->AddFormInput('Password', new TextLine(esc_html__('Password:', 'read-more-login'), '', 'password', ['type' => 'password']));
        /* translators: Login form, checkbox label. */
        $this->AddFormInput('Remember', new CheckBox(esc_html__('Remember me', 'read-more-login'), 0, 'remember'));

        /* translators: Button label. */
        $this->AddButton('ButtonLogin', new PushButton(esc_html__('Log in', 'read-more-login'), PushButton::METHOD_POST));

        $linking_options = new SettingsLinkingOptions();
        $register_url = $linking_options->GetRegisterUrl();

        /* translators: Link label. */
        $this->AddFooter('Link2', new Link($register_url, esc_html__('Register', 'read-more-login')));

        $lost_password_url = $linking_options->GetLostPasswordUrl();
        /* translators: Link label. */
        $this->AddFooter('Link1', new Link($lost_password_url, esc_html__('Forgotten username or password?', 'read-more-login')));

        $this->AddFooter('StatusBarFooter', new StatusBar());
    }

    public function DrawView($parameters = null)
    {
        if(is_array($parameters))
        {
            if(isset($parameters['status_message']))
            {
                if ($parameters['status_message'])
                {
                    $this->StatusBarHeader->SetStatusHtml($parameters['status_message'], StatusBar::STATUS_SUCCESS);
                }
            }
        }

        return parent::DrawView($parameters);
    }
}
