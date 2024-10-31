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

use WP_PluginFramework\Views\AdminPanelStdView;
use WP_PluginFramework\HtmlComponents\TextLine;
use WP_PluginFramework\HtmlComponents\PushButton;
use WP_PluginFramework\HtmlComponents\Link;
use WP_PluginFramework\HtmlComponents\StatusBar;

class AdminStatisticsView extends AdminPanelStdView
{
    /** @var TextLine */
    public $TextUserLogin;
    /** @var TextLine */
    public $TextFirstName;
    /** @var TextLine */
    public $TextLastName;
    /** @var TextLine */
    public $TextEmail;
    /** @var PushButton */
    public $ButtonUpdate;
    /** @var Link */
    public $LinkChangePassword;
    /** @var StatusBar */
    public $StatusBarFooter;

    public function __construct($id, $controller)
    {
        $this->AddComponent('TextUserLogin', new TextLine('Username:'));
        $this->AddComponent('TextFirstName', new TextLine('First name:'));
        $this->AddComponent('TextLastName', new TextLine('Last name:'));
        $this->AddComponent('TextEmail', new TextLine('E-mail:'));
        $this->AddComponent('ButtonUpdate', new PushButton('Save'));
        $this->AddComponent('$LinkChangePassword', new Link('/password', 'Change password'));
        $this->AddComponent('StatusBarFooter', new StatusBar());

        parent::__construct($id, $controller);
    }

    public function CreateContent($parameters=null, $wrapper=null)
    {
        $this->TextUserLogin->SetText($parameters['user_login']);
        $this->TextFirstName->SetText($parameters['first_name']);
        $this->TextLastName->SetText($parameters['last_name']);
        $this->TextEmail->SetText($parameters['user_email']);

        return parent::CreateContent($parameters, $wrapper);
    }
}
