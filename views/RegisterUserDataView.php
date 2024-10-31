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
use WP_PluginFramework\HtmlComponents\StatusBar;
use WP_PluginFramework\HtmlElements\P;
use WP_PluginFramework\HtmlElements\H;

class RegisterUserDataView extends FrontPageView
{
    /** @var StatusBar */
    public $StatusBarHeader;
    /** @var TextLine */
    public $Username;
    /** @var TextLine */
    public $Password;
    /** @var TextLine */
    public $FirstName;
    /** @var TextLine */
    public $LastName;
    /** @var PushButton */
    public $ButtonRegister;
    /** @var StatusBar */
    public $StatusBarFooter;

    public function __construct($id, $controller)
    {
        parent::__construct($id, $controller);

        $this->SetMethod(self::SEND_METHOD_POST);

        /* translators: Headline for registration form. */
        $h = new H(3, esc_html__('Complete your registration', 'read-more-login'));
        $this->AddHeader('header', $h);

        $this->AddHeader('StatusBarHeader', new StatusBar());

        $p2 = new P(esc_html__('Please select your username and password.', 'read-more-login'));
        $this->AddHeader('header2', $p2);

        $this->AddFormInput('Username', new TextLine(esc_html__('Username:', 'read-more-login'), '', 'username'));
        $this->AddFormInput('Password', new TextLine(esc_html__('Password:', 'read-more-login'), '', 'password', ['type' => 'password']));

        $register_option = get_option(SettingsFormOptions::OPTION_NAME);
        if($register_option[SettingsFormOptions::REGISTER_COLLECT_FIRST_NAME])
        {
            /* translators: A person's first name. */
            $this->AddFormInput('FirstName', new TextLine(esc_html__('First name:', 'read-more-login'), '', 'first_name'));
        }

        if($register_option[SettingsFormOptions::REGISTER_COLLECT_LAST_NAME])
        {
            /* translators: A person's last name. */
            $this->AddFormInput('LastName', new TextLine(esc_html__('Last name:', 'read-more-login'), '', 'last_name'));
        }

        /* translators: Button label, start register as new user. */
        $this->AddButton('ButtonRegister', new PushButton(esc_html__('Register', 'read-more-login'),PushButton::METHOD_POST));

        $this->AddFooter('StatusBarFooter', new StatusBar());
    }

    public function CreateContent($parameters=null, $wrapper=null)
    {
        return parent::CreateContent($parameters, $wrapper);
    }
}
