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

class CompactWidget
{
    static public function DrawCompactWidget()
    {
        $show_name = '';

        if (is_user_logged_in())
        {
            $wp_user_id = get_current_user_id();
            $user_info = get_userdata($wp_user_id);
            $username = $user_info->user_login;
            $first_name = $user_info->first_name;
            $last_name = $user_info->last_name;

            if ($first_name == '' and $last_name == '')
            {
                $show_name = $username;
            }
            else
            {
                $show_name = $first_name . ' ' . $last_name;
            }

            $login_widget_style = 'style="display:none;"';
            $logout_widget_style = ' ';
        }
        else
        {
            $login_widget_style = '';
            $logout_widget_style = ' style="display:none;"';
        }

        $output = '<div class="aru_rml_compact_widget">';

        $links_options = new SettingsLinkingOptions();
        $profile_url = $links_options->GetCompleteLinkUrl(SettingsLinkingOptions::PROFILE_PAGE_LINK);
        $linking_options = new SettingsLinkingOptions();
        $register_url = $linking_options->GetRegisterUrl();
        $login_url = $linking_options->GetLoginUrl();

        /* translators: Link text for compact widget. Translation should be short. */
        $login_txt = esc_html__('Login', 'read-more-login');
        /* translators: Link text for compact widget. Translation should be short. */
        $register_txt = esc_html__('Register', 'read-more-login');
        /* translators: Link text for compact widget. Translation should be short. */
        $logout_txt = esc_html__('Logout', 'read-more-login');
        /* translators: Link text for compact widget. Translation should be short. */
        $profile_txt = esc_html__('Profile', 'read-more-login');

        $output .= '<div class="aru_rml_login_widget" ' . $logout_widget_style . '><span class="aru_rml_show_name">' . $show_name . '</span> <a class="aru_rml_logout_href" href="' . wp_logout_url() . ' ">'.$logout_txt.'</a> <a href="' . $profile_url . '">'.$profile_txt.'</a></div>';
        $output .= '<div class="aru_rml_logout_widget" ' . $login_widget_style . '><a href="' . $login_url . '">'.$login_txt.'</a> <a href="' . $register_url . '">'.$register_txt.'</a></div>';
        $output .= '</div>';

        return $output;
    }
}
