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

class ProfileView extends FrontPageView
{
    /** @var StatusBar */
    public $StatusBarHeader;
    /** @var TextLine */
    public $TextUserLogin;
    /** @var TextLine */
    public $TextFirstName;
    /** @var TextLine */
    public $TextLastName;
    /** @var TextLine */
    public $TextEmail;
    /** @var PushButton */
    public $StdSubmit;
    /** @var Link */
    public $LinkChangePassword;
    /** @var StatusBar */
    public $StatusBarFooter;

    public function __construct($id, $controller)
    {
        parent::__construct($id, $controller);

        $this->AddHeader('StatusBarHeader', new StatusBar());

        $prop['readonly'] = true;
        $this->AddFormInput('TextUserLogin', new TextLine(esc_html__('Username', 'read-more-login'), '', 'user_login', $prop));
        $this->AddFormInput('TextFirstName', new TextLine(esc_html__('First name', 'read-more-login'), '', 'first_name'));
        $this->AddFormInput('TextLastName', new TextLine(esc_html__('Last name', 'read-more-login'), '', 'last_name'));
        $this->AddFormInput('TextEmail', new TextLine(esc_html__('E-mail', 'read-more-login'), '', 'user_email'));

        /* translators: Button label. */
        $this->AddButton('StdSubmit', new PushButton(esc_html__('Save changes', 'read-more-login')));

        $linking_options = new SettingsLinkingOptions();
        $lost_password_url = $linking_options->GetLostPasswordUrl();
        /* translators: Link label. */
        $this->AddFooter('LinkChangePassword', new Link($lost_password_url, esc_html__('Change password', 'read-more-login')));
        $this->AddFooter('StatusBarFooter', new StatusBar());
    }

    public function CreateContent($parameters=null, $wrapper=null)
    {
        $this->TextUserLogin->SetProperty('description', esc_html__('Usernames cannot be changed.', 'read-more-login'));

        return parent::CreateContent($parameters, $wrapper);
    }
}
