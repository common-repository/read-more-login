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

use WP_PluginFramework\HtmlComponents\StatusBar;
use WP_PluginFramework\HtmlElements\H;
use WP_PluginFramework\HtmlElements\P;
use WP_PluginFramework\HtmlElements\Div;

class ReadMoreCheckEmailView extends ReadMoreView
{
    /** @var H */
    public $Header;
    /** @var P */
    public $Message;

    public function __construct($id, $controller)
    {
        parent::__construct($id, $controller);

        $this->ContentConfig['form_input_encapsulation'] = 'table';

        $header = new H(2);
        $this->AddHeader('Header', $header);

        $div = new Div();
        $this->AddHeader('Message', $div);

        $this->AddFooter('StatusBarFooter', new StatusBar(StatusBar::TYPE_INLINE_TEXT, null, StatusBar::STATUS_SUCCESS));

        $this->ShowForm = false;
    }
}
