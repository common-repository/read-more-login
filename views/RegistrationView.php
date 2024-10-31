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
use WP_PluginFramework\HtmlComponents\Link;
use WP_PluginFramework\HtmlElements\P;

class RegistrationView extends FrontPageView
{
    /** @var TextLine */
    public $Email;
    /** @var CheckBox */
    public $AcceptTerms;
    /** @var PushButton */
    public $Registration;
    /** @var StatusBar */
    public $StatusBarFooter;

    public function __construct($id, $controller)
    {
        parent::__construct($id, $controller);

        $this->AddHeader('StatusBarHeader', new StatusBar());

        $message = new P(esc_html__('Enter your e-mail address to register.', 'read-more-login'));
        $this->AddHeader('msg', $message);

        $this->AddFormInput('Email', new TextLine(esc_html__('E-mail address:', 'read-more-login'), ''));

        $design_options = get_option(SettingsFormOptions::OPTION_NAME);
        if($design_options[SettingsFormOptions::REGISTER_MUST_ACCEPT_TERMS])
        {
            $checkbox = $this->MakeTermsCheckbox();
            $this->AddFormInput('AcceptTerms', $checkbox);
        }

        /* translators: Button label, start register as new user. */
        $this->AddButton('ButtonStartRegistration', new PushButton(esc_html__('Register', 'read-more-login')));

        $linking_options = new SettingsLinkingOptions();
        $login_url = $linking_options->GetLoginUrl();
        $lost_password_url = $linking_options->GetLostPasswordUrl();

        $this->AddFooter('Link1', new Link($login_url, esc_html__('Log in', 'read-more-login')));

        $this->AddFooter('Link2', new Link($lost_password_url, esc_html__('Forgotten username or password?', 'read-more-login')));

        $this->AddFooter('StatusBarFooter', new StatusBar());
    }
}
