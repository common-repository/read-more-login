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

use WP_PluginFramework\Views\AdminPanelStdView;
use WP_PluginFramework\HtmlElements\Div;
use WP_PluginFramework\HtmlElements\Table;
use WP_PluginFramework\HtmlElements\Td;
use WP_PluginFramework\HtmlElements\Th;
use WP_PluginFramework\HtmlElements\Tr;

class AdminPanelView extends AdminPanelStdView
{
    public function CreateContent($parameters=null, $wrapper=null)
    {
        /* translators: Name of the plugin, should only be translated if done consistently. */
        $this->SetHeader(esc_html__('Read More Login', 'read-more-login') . ' - ' . get_admin_page_title());

        $wizard_attributes = array(
            'id' => 'rml_wizard_placeholder',
            'class' => 'rml_wizard_placeholder'
        );

        $this->TopHeaderSection = new Div(null, $wizard_attributes);

        $wizard_options = get_option(WizardOptions::OPTION_NAME);
        if($wizard_options[WizardOptions::SHOW_CONFIG_WIZARD])
        {
            $wizard_controller = new WizardController();
            $wizard_html = $wizard_controller->Draw();
            if($wizard_html)
            {
                $this->TopHeaderSection->AddHtml($wizard_html);
            }
        }

        parent::CreateContent($parameters, $wrapper);
    }

    public function GetVariableDescriptions($is_form)
    {
        $table_attr = array('class' => 'rml_admin_table');
        $table = new Table(null, $table_attr);
        $tr = new Tr();
        $tr->AddContent(new Th('Variables'));
        $tr->AddContent(new Th('Description'));
        $table->AddContent($tr);

        $tr = new Tr();
        $tr->AddContent(new Td('%username%'));
        $tr->AddContent(new Td(esc_html__('Variable will put in the user\'s username. This variable is not available before the username has been given during the registration.', 'read-more-login')));
        $table->AddContent($tr);

        $tr = new Tr();
        $tr->AddContent(new Td('%first_name%'));
        $tr->AddContent(new Td(esc_html__('Variable will put in user\'s first name. If not given it will write blank.', 'read-more-login')));
        $table->AddContent($tr);

        $tr = new Tr();
        $tr->AddContent(new Td('%last_name%'));
        $tr->AddContent(new Td(esc_html__('Variable will put in user\'s last name. If not given it will write blank.', 'read-more-login')));
        $table->AddContent($tr);

        $tr = new Tr();
        $tr->AddContent(new Td('%name%'));
        $tr->AddContent(new Td(esc_html__('Depending of what is available the variable will put in the user\'s first name and/or the last name. If no name has been given, variable will write the user\'s username.', 'read-more-login')));
        $table->AddContent($tr);

        $tr = new Tr();
        $tr->AddContent(new Td('%email%'));
        $tr->AddContent(new Td(esc_html__('Variable will put in the user\'s e-mail address.', 'read-more-login')));
        $table->AddContent($tr);

        $tr = new Tr();
        $tr->AddContent(new Td('%title%'));
        $tr->AddContent(new Td(esc_html__('Variable will put in the tittle of page the user are registering on.', 'read-more-login')));
        $table->AddContent($tr);

        $tr = new Tr();
        $tr->AddContent(new Td('%site_name%'));
        $tr->AddContent(new Td(esc_html__('Variable will put in the name of the site.', 'read-more-login')));
        $table->AddContent($tr);

        $tr = new Tr();
        $tr->AddContent(new Td('%site_url%'));
        $tr->AddContent(new Td(esc_html__('Variable will put in the link to the site.', 'read-more-login')));
        $table->AddContent($tr);

        if(!$is_form)
        {
            $tr = new Tr();
            $tr->AddContent(new Td('%link%'));
            $tr->AddContent(new Td(esc_html__('Depending on type of e-mail this variable will put in link to read the remaining article or to complete the registration or the password change page.', 'read-more-login')));
            $table->AddContent($tr);
        }

        return $table;
    }
}
