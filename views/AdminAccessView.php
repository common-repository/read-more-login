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

class AdminAccessView extends AdminPanelView
{
    const GOOGLE_READ = 'google_read';

    /** @var CheckBox */
    public $google_read;
    /** @var PushButton */
    public $StdSubmit;

    public function __construct($id, $controller)
    {
        parent::__construct($id, $controller);

        /* translators: Admin panel headline */
        $this->AddHeader('Header1', esc_html__('Access settings', 'read-more-login'));

        $this->AddFormInput('google_read', new CheckBox());

        /* translators: Button label */
        $this->AddButton('StdSubmit', new PushButton(esc_html__('Save changes', 'read-more-login')));
    }

    public function CreateContent($parameters = null, $wrapper = null)
    {
        if (!isset($wrapper))
        {
            $wrapper = $this;
        }

        $this->google_read->SetProperty('label', esc_html__('Search engine access:', 'read-more-login'));
        $items = array();
        $items[SettingsAccessOptions::GOOGLE_READ] = esc_html__('Visitors from search engines like Google can read protected content without login.', 'read-more-login');
        $this->google_read->SetProperty('Items', $items);
        $this->google_read->SetProperty('description', esc_html__('This will also allow search engines to read and index your entire page and could improve your ratings on searches.', 'read-more-login'));

        $this->StdSubmit->SetPrimary(true);

        parent::CreateContent($parameters, $wrapper);
    }
}
