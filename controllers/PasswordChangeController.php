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

use WP_PluginFramework\Utils\SecurityFilter;
use WP_PluginFramework\HtmlComponents\StatusBar;

class PasswordChangeController extends MembershipController
{
    /** @var PasswordChangeView */
    public $View;

    public function __construct()
    {
        $secret = null;
        $secret_required=false;

        $action = SecurityFilter::SafeReadPostRequest('action', SecurityFilter::STRING_KEY_NAME);
        if($action == ReadMoreLoginPlugin::WP_PLUGIN_FRAMEWORK_AJAX_HANDLER)
        {
            $event  = SecurityFilter::SafeReadPostRequest('_event', SecurityFilter::STRING_KEY_NAME);
            $reg_id = SecurityFilter::SafeReadPostRequest(self::REG_ID, SecurityFilter::POSITIVE_INTEGER_ZERO);
            $nonce  = SecurityFilter::SafeReadPostRequest(self::REG_NONCE, SecurityFilter::ALPHA_NUM);
            $post_id = SecurityFilter::SafeReadPostRequest(self::REG_POST_ID, SecurityFilter::POSITIVE_INTEGER);
        }
        else
        {
            $event  = SecurityFilter::SafeReadGetRequest(self::REG_EVENT, SecurityFilter::STRING_KEY_NAME);
            $reg_id = SecurityFilter::SafeReadGetRequest(self::REG_ID, SecurityFilter::POSITIVE_INTEGER_ZERO);
            $nonce  = SecurityFilter::SafeReadGetRequest(self::REG_NONCE, SecurityFilter::ALPHA_NUM);
            $post_id = get_the_ID();
        }

        $reg_type = RegistrationDbTable::REG_TYPE_PASSWORD_RECOVERY;

        parent::__construct('ARU_ReadMoreLogin\PasswordChangeView', $event, $reg_id, $reg_type, $post_id, $nonce, $secret, $secret_required);

        $this->SetPermission(true);
    }

    public function ButtonSavePassword_click()
    {
        $this->HideInputErrorIndications();

        $password = $this->View->Password1->GetText();
        $confirm_password = $this->View->Password2->GetText();

        if($this->CheckPasswordChangeData($password, $confirm_password))
        {
            $wp_user_id = get_current_user_id();
            if($this->UpdatePassword($password, $wp_user_id))
            {
                $this->ReloadView('ARU_ReadMoreLogin\GeneralMessageView');
                $this->View->StatusBarFooter->SetStatusText(esc_html__('Password has been changed.', 'read-more-login'), StatusBar::STATUS_SUCCESS);


                /* Log-out nonce will change after password change, need update existing log-out
                   links in the browser next time ajax is loaded. */
                $logout_url = wp_logout_url();
                $logout_url = str_replace('&amp;', '&', $logout_url);
                $this->View->UpdateClientDom('a.aru_rml_logout_href', 'attr', array('href', $logout_url));
            }
            else
            {
                $this->View->StatusBarFooter->SetStatusText(esc_html__('Error saving password. Please retry with another password.', 'read-more-login'), StatusBar::STATUS_SUCCESS);
            }
        }
        else
        {
            $message = $this->GetServerContextData('response_message');
            $this->View->StatusBarFooter->SetStatusText($message, StatusBar::STATUS_ERROR);
        }

        $this->ShowInputErrorIndications();
    }
}
