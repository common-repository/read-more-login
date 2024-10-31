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

use WP_PluginFramework\PluginContainer;
use WP_PluginFramework\Controllers\StdController;

class WizardController extends StdController
{
    /** @var WizardView */
    public $View;

    public function __construct()
    {
        parent::__construct('ARU_ReadMoreLogin\WizardOptions', 'ARU_ReadMoreLogin\WizardView');
        $this->SetPermission(true);
    }

    public function CloseButton_click()
    {
        $this->Model->SetData(WizardOptions::SHOW_CONFIG_WIZARD, false);
        $this->Model->SaveData();
        $this->View->Remove();

        $selector = '#rml_wizard_tools_options input#rml_show_config_wizard';
        $method = 'removeAttr';
        $arg = array('checked');
        $this->View->UpdateClientDom($selector, $method, $arg);
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
