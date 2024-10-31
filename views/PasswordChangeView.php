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

class PasswordChangeView extends FrontPageView
{
    /** @var StatusBar */
    public $StatusBarHeader;
    /** @var TextLine */
    public $Password1;
    /** @var TextLine */
    public $Password2;
    /** @var PushButton */
    public $ButtonSavePassword;
    /** @var StatusBar */
    public $StatusBarFooter;

    public function __construct($id, $controller)
    {

        parent::__construct($id, $controller);

        $this->AddHeader('StatusBarHeader', new StatusBar());

        $this->AddFormInput('Password1', new TextLine(esc_html__('New password:', 'read-more-login'), '', 'password', ['type' => 'password']));
        $this->AddFormInput('Password2', new TextLine(esc_html__('Confirm password:', 'read-more-login'), '', 'confirm_password', ['type' => 'password']));

        /* translators: Button label. */
        $this->AddButton('ButtonSavePassword', new PushButton(esc_html__('Save changes', 'read-more-login')));

        $this->AddFooter('StatusBarFooter', new StatusBar());
    }
}
