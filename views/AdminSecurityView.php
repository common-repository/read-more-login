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
use WP_PluginFramework\HtmlComponents\PushButton;

class AdminSecurityView extends AdminPanelView
{
    /** @var CheckBox */
    public $username_not_email;
    /** @var PushButton */
    public $StdSubmit;

    public function __construct($id, $controller)
    {
        parent::__construct($id, $controller);

        /* translators: Admin panel headline */
        $this->AddHeader('Header1', esc_html__('Security settings', 'read-more-login'));

        $this->AddFormInput('username_not_email', new CheckBox());

        /* translators: Button label */
        $this->AddButton('StdSubmit', new PushButton(esc_html__('Save changes', 'read-more-login')));
    }

    public function CreateContent($parameters = null, $wrapper = null)
    {
        if (!isset($wrapper))
        {
            $wrapper = $this;
        }

        $this->username_not_email->SetProperty('label', esc_html__('E-mail as username:', 'read-more-login'));
        $items = array();
        $items[SettingsSecurityOptions::USERNAME_NOT_EMAIL] = esc_html__('Forbid users to register using their e-mail address as username.', 'read-more-login');
        $this->username_not_email->SetProperty('Items', $items);
        $this->username_not_email->SetProperty('description', esc_html__('If a visitor tries to register using an e-mail address as username, he will be asked to correct it by enter a non-email.', 'read-more-login'));

        $this->StdSubmit->SetPrimary(true);

        parent::CreateContent($parameters, $wrapper);
    }
}
