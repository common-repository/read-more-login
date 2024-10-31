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
use WP_PluginFramework\Utils\SecurityFilter;

class AdminSettingsPreviewFormController extends AdminSettingsTabController
{
    /** @var AdminFormPreView */
    protected $View;

    /** @var ReadMoreLoginController | MembershipController */
    protected $PreviewController = null;

    public function HandleSaveSuccess($data_record)
    {
        $this->View->AdminStatusBar->SetStatusText(esc_html__('Your settings have been saved.', 'read-more-login'), StatusBar::STATUS_SUCCESS);

        $preview_selected = SecurityFilter::SafeReadPostRequest('preview', SecurityFilter::STRING_KEY_NAME);
        if(!$preview_selected){
            $preview_selected = 'login_form';
        }

        $inner_div = $this->View->CreatePreview($preview_selected);
        $html = $inner_div->DrawHtml();

        $selector = 'div#rml_design_studio';
        $this->View->UpdateClientDom($selector, 'replaceWith', $html);
    }
}
