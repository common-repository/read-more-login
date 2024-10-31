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

use WP_PluginFramework\HtmlComponents\HtmlText;
use WP_PluginFramework\HtmlComponents\PushButton;
use WP_PluginFramework\HtmlComponents\Link;
use WP_PluginFramework\HtmlComponents\StatusBar;
use WP_PluginFramework\HtmlElements\P;
use WP_PluginFramework\HtmlElements\Br;
use WP_PluginFramework\HtmlElements\Strong;

class LogoutView extends FrontPageView
{
    public function __construct($id, $controller)
    {
        parent::__construct($id, $controller);

        $this->SetMethod(self::SEND_METHOD_POST);

        $this->AddHeader('StatusBarHeader', new StatusBar());

        $message = new P(esc_html__('You are now logged in.', 'read-more-login'));
        $message->AddContent(new Br());

        $current_user = wp_get_current_user();

        $s = new Strong($current_user->user_login);
        $formatted_username = $s->DrawHtml();
        /* translators: %s: username. */
        $msg = sprintf(esc_html__('Username: %s', 'read-more-login'), $formatted_username);
        $html = new HtmlText($msg);
        $message->AddContent($html);

        $this->AddHeader('Message', $message);

        /* translators: Button label. */
        $this->AddButton('ButtonLogout', new PushButton(esc_html__('Log out', 'read-more-login'), PushButton::METHOD_POST));

        $links_options = new SettingsLinkingOptions();
        $url = $links_options->GetCompleteLinkUrl(SettingsLinkingOptions::PROFILE_PAGE_LINK);
        /* translators: Link label. */
        $this->AddFooter('Link1', new Link($url, esc_html__('Update your profile', 'read-more-login')));

        $this->AddFooter('StatusBarFooter', new StatusBar());
    }
}
