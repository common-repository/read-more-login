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
use WP_PluginFramework\HtmlComponents\CheckBox;
use WP_PluginFramework\HtmlComponents\PushButton;
use WP_PluginFramework\HtmlComponents\StatusBar;
use WP_PluginFramework\HtmlElements\Div;
use WP_PluginFramework\HtmlElements\H;
use WP_PluginFramework\HtmlElements\P;


class ReadMoreRegistrationView extends ReadMoreView
{
    /** @var H */
    public $Header;
    /** @var StatusBar */
    public $StatusBarHeader;
    /** @var P */
    public $Message;
    /** @var TextLine */
    public $Email;
    /** @var TextLine */
    public $FirstName;
    /** @var TextLine */
    public $LastName;
    /** @var CheckBox */
    public $AcceptTerms;
    /** @var PushButton */
    public $ButtonRegisterEmail;
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

        $this->AddHeader('StatusBarHeader', new StatusBar());

        $this->AddFormInput('Email', new TextLine(esc_html__('E-mail address:', 'read-more-login'), '', 'email'));

        if($design_options[SettingsFormOptions::REGISTER_COLLECT_FIRST_NAME])
        {
            /* translators: A person's first name. */
            $this->AddFormInput('FirstName', new TextLine(esc_html__('First name:', 'read-more-login'), '', 'first_name'));
        }

        if($design_options[SettingsFormOptions::REGISTER_COLLECT_LAST_NAME])
        {
            /* translators: A person's last name. */
            $this->AddFormInput('LastName', new TextLine(esc_html__('Last name:', 'read-more-login'), '', 'last_name'));
        }

        if($design_options[SettingsFormOptions::REGISTER_MUST_ACCEPT_TERMS])
        {
            $checkbox = $this->MakeTermsCheckbox();
            $this->AddFormInput('AcceptTerms', $checkbox);
        }

        /* translators: Button label, start register as new user. */
        $this->AddButton('ButtonRegisterEmail', new PushButton(esc_html__('Register', 'read-more-login')));

        $this->AddFooter('StatusBarFooter', new StatusBar());
    }
}
