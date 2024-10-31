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

use WP_PluginFramework\Controllers\AdminController;

class AdminSettingsTabController extends AdminController
{
    protected $TabName;

    public function __construct($model, $view)
    {
        parent::__construct($model, $view);
    }

    protected function InitView($values)
    {
        $this->View->SetTabName($this->TabName);

        /* translators: Admin tab menu. Limited space, keep translation short. */
        $this->View->AddNavTab(['name' => 'forms', 'text' => esc_html__('Forms', 'read-more-login'), 'link' => site_url().'/wp-admin/admin.php?page=rml-admin-menu&tab=forms']);
        /* translators: Admin tab menu. Limited space, keep translation short. */
        $this->View->AddNavTab(['name' => 'style', 'text' => esc_html__('Style', 'read-more-login'), 'link' => site_url().'/wp-admin/admin.php?page=rml-admin-menu&tab=style']);
        /* translators: Admin tab menu. Limited space, keep translation short. */
        $this->View->AddNavTab(['name' => 'email', 'text' => esc_html__('E-mail', 'read-more-login'), 'link' => site_url().'/wp-admin/admin.php?page=rml-admin-menu&tab=email']);
        /* translators: Admin tab menu. Limited space, keep translation short. */
        $this->View->AddNavTab(['name' => 'linking', 'text' => esc_html__('Page linking', 'read-more-login'), 'link' => site_url().'/wp-admin/admin.php?page=rml-admin-menu&tab=linking']);
        /* translators: Admin tab menu. Abbreviation for Search Engine Optimization. Limited space, keep translation short. */
        $this->View->AddNavTab(['name' => 'access', 'text' => esc_html__('Access', 'read-more-login'), 'link' => site_url().'/wp-admin/admin.php?page=rml-admin-menu&tab=access']);
        /* translators: Admin tab menu. Limited space, keep translation short. */
        $this->View->AddNavTab(['name' => 'security', 'text' => esc_html__('Security', 'read-more-login'), 'link' => site_url().'/wp-admin/admin.php?page=rml-admin-menu&tab=security']);
        /* translators: Admin tab menu. Limited space, keep translation short. */
        $this->View->AddNavTab(['name' => 'advanced', 'text' => esc_html__('Advanced', 'read-more-login'), 'link' => site_url().'/wp-admin/admin.php?page=rml-admin-menu&tab=advanced']);

        parent::InitView($values);
    }
}
