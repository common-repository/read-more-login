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

use WP_PluginFramework\Controllers\StdController;
use WP_PluginFramework\HtmlComponents\StatusBar;
use WP_PluginFramework\PluginContainer;

class ProfileController extends StdController
{
    /** @var ProfileView */
    public $View;

    public function __construct()
    {
        parent::__construct('ARU_ReadMoreLogin\ProfileModel', 'ARU_ReadMoreLogin\ProfileView');
        $this->SetPermission(true);
    }

    protected function ReadNonceProtectedData($event_source)
    {
        $nonce_protected_data = parent::ReadNonceProtectedData($event_source);

        if ($this->GetServerContextData('have_logged_in'))
        {
            $nonce_protected_data = $this->GetNonceProtectedData();
            $nonce_protected_data = $this->CalculateWpNonce($nonce_protected_data);

            $this->RegisterEvent('ButtonLogin', 'click', 'post');
        }

        return $nonce_protected_data;
    }

    protected function LoadValues($values = array())
    {
        if ($this->GetServerContextData('have_logged_in'))
        {
            return $this->LoadModelValues($values);
        }
        else
        {
            return parent::LoadValues($values);
        }
    }

    public function HandleSaveSuccess($data_record)
    {
        $this->View->StatusBarFooter->SetStatusText(esc_html__('Your profile has been updated.', 'read-more-login'), StatusBar::STATUS_SUCCESS);

        /* Update compact widget with new username. */
        /* Select login username or full name if it exists. */
        $wp_user_id = get_current_user_id();
        $user_info = get_userdata($wp_user_id);
        $username = $user_info->user_login;
        $first_name = $user_info->first_name;
        $last_name = $user_info->last_name;

        if (($first_name) == '' and ($last_name == ''))
        {
            $display_name = $username;
        }
        else
        {
            $display_name = $first_name . ' ' . $last_name;
        }

        $this->View->UpdateClientDom('div.aru_rml_login_widget span', 'text', $display_name);
    }

    public function ButtonLogin_click()
    {

    }

    protected function EnqueueScript()
    {
        parent::EnqueueScript();

        $plugin_version = PluginContainer::GetPluginVersion();

        $style_handler = 'aru_readmorelogin_plugin_style_handler';
        $style_url = plugins_url() . '/read-more-login/css/rml_style.css';
        wp_enqueue_style($style_handler, $style_url, array(), $plugin_version);
    }
}
