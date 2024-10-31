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

use WP_PluginFramework\HtmlElements\Div;

class AdminPages
{
    static $admin_menu_page;
    static $admin_info_page;

    static public function AdminMenu()
    {
        $icon = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTkiIGhlaWdodD0iMTciIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CiA8IS0tIENyZWF0ZWQgd2l0aCBNZXRob2QgRHJhdyAtIGh0dHA6Ly9naXRodWIuY29tL2R1b3BpeGVsL01ldGhvZC1EcmF3LyAtLT4KIDxnPgogIDx0aXRsZT5iYWNrZ3JvdW5kPC90aXRsZT4KICA8cmVjdCBmaWxsPSIjZmZmIiBpZD0iY2FudmFzX2JhY2tncm91bmQiIGhlaWdodD0iMTkiIHdpZHRoPSIyMSIgeT0iLTEiIHg9Ii0xIi8+CiAgPGcgZGlzcGxheT0ibm9uZSIgb3ZlcmZsb3c9InZpc2libGUiIHk9IjAiIHg9IjAiIGhlaWdodD0iMTAwJSIgd2lkdGg9IjEwMCUiIGlkPSJjYW52YXNHcmlkIj4KICAgPHJlY3QgZmlsbD0idXJsKCNncmlkcGF0dGVybikiIHN0cm9rZS13aWR0aD0iMCIgeT0iMCIgeD0iMCIgaGVpZ2h0PSIxMDAlIiB3aWR0aD0iMTAwJSIvPgogIDwvZz4KIDwvZz4KIDxnPgogIDx0aXRsZT5MYXllciAxPC90aXRsZT4KICA8cmVjdCBzdHJva2U9IiMwMDAiIGlkPSJzdmdfMiIgaGVpZ2h0PSIyLjQxODYwNSIgd2lkdGg9IjE0LjY5NzY3NSIgeT0iMi42ODAyMzMiIHg9IjIuMTk3Njc0IiBmaWxsLW9wYWNpdHk9Im51bGwiIHN0cm9rZS1vcGFjaXR5PSJudWxsIiBmaWxsPSIjZmZmIi8+CiAgPGxpbmUgc3Ryb2tlPSIjMDAwIiBzdHJva2UtbGluZWNhcD0ibnVsbCIgc3Ryb2tlLWxpbmVqb2luPSJudWxsIiBpZD0ic3ZnXzYiIHkyPSIxMy4yODQ4ODQiIHgyPSI3LjU5MzAyMiIgeTE9IjEzLjI4NDg4NCIgeDE9IjIuMTk3NjczIiBmaWxsLW9wYWNpdHk9Im51bGwiIHN0cm9rZS1vcGFjaXR5PSJudWxsIiBzdHJva2Utd2lkdGg9IjMiIGZpbGw9Im5vbmUiLz4KICA8cmVjdCBzdHJva2U9IiMwMDAiIGlkPSJzdmdfOCIgaGVpZ2h0PSIyLjQxODYwNSIgd2lkdGg9IjE0LjY5NzY3NSIgeT0iNy4yMzgzNzIiIHg9IjIuMTk3Njc0IiBmaWxsLW9wYWNpdHk9Im51bGwiIHN0cm9rZS1vcGFjaXR5PSJudWxsIiBmaWxsPSIjZmZmIi8+CiAgPGxpbmUgc3Ryb2tlPSIjMDAwIiBzdHJva2UtbGluZWNhcD0ibnVsbCIgc3Ryb2tlLWxpbmVqb2luPSJudWxsIiBpZD0ic3ZnXzkiIHkyPSIxMy4yODQ4ODQiIHgyPSIxNS4xMjc5MDYiIHkxPSIxMy4yODQ4ODQiIHgxPSI5LjczMjU1NyIgZmlsbC1vcGFjaXR5PSJudWxsIiBzdHJva2Utb3BhY2l0eT0ibnVsbCIgc3Ryb2tlLXdpZHRoPSIzIiBmaWxsPSJub25lIi8+CiA8L2c+Cjwvc3ZnPg==';

        add_menu_page(
            'rml-admin',
            'Read More Login',
            'manage_options',
            'rml-admin-menu',
            array('ARU_ReadMoreLogin\AdminPages', 'AdminSettingsPage'),
            $icon
        );

        self::$admin_menu_page = add_submenu_page(
            'rml-admin-menu',
            esc_html__('Settings', 'read-more-login'),
            esc_html__('Settings', 'read-more-login'),
            'manage_options',
            'rml-admin-menu',
            array('ARU_ReadMoreLogin\AdminPages', 'AdminSettingsPage')
        );

        add_submenu_page(
            'rml-admin-menu',
            esc_html__('Status', 'read-more-login'),
            esc_html__('Status', 'read-more-login'),
            'manage_options',
            'rml-admin-registration-status',
            array('ARU_ReadMoreLogin\AdminPages', 'StatusPage')
        );

        add_submenu_page(
            'rml-admin-menu',
            esc_html__('Statistics', 'read-more-login'),
            esc_html__('Statistics', 'read-more-login'),
            'manage_options',
            'rml-admin-statistics',
            array('ARU_ReadMoreLogin\AdminPages', 'StatisticsPage')
        );

        self::$admin_info_page = add_submenu_page(
            'rml-admin-menu',
            esc_html__('Help', 'read-more-login'),
            esc_html__('Help', 'read-more-login'),
            'manage_options',
            'rml-admin-help',
            array('ARU_ReadMoreLogin\AdminPages', 'AdminPluginInfoPage')
        );
    }

    static public function AdminSettingsPage()
    {
        $panel = null;

        if(isset($_GET['tab']))
        {
            switch ($_GET['tab'])
            {
                case 'design':
                    $panel = new AdminSettingsFormController();
                    break;

                case 'style':
                    $panel = new AdminSettingsStyleController();
                    break;

                case 'linking':
                    $panel = new AdminSettingsLinkingController();
                    break;

                case 'email':
                    $panel = new AdminSettingsEmailController();
                    break;

                case 'access':
                    $panel = new AdminSettingsAccessController();
                    break;

                case 'security':
                    $panel = new AdminSettingsSecurityController();
                    break;

                case 'advanced':
                    $panel = new AdminSettingsAdvancedController();
                    break;
            }
        }

        if(!isset($panel))
        {
            $panel = new AdminSettingsFormController();
        }

        $html = $panel->Draw();

        echo $html;
    }

    static public function ScreenSettings($status, $args)
    {
        $return = $status;
        if(($args->base == self::$admin_menu_page) || ($args->base == self::$admin_info_page))
        {
            $wizard_options = get_option(WizardOptions::OPTION_NAME);

            $show_config_wizard_checked = '';
            if($wizard_options[WizardOptions::SHOW_CONFIG_WIZARD])
            {
                $show_config_wizard_checked = 'checked="1"';
            }

            /* translators: Button label. */
            $button = get_submit_button( esc_html__('Apply', 'read-more-login' ), 'button button-primary', 'screen-options-apply', false );
            $return .= "
            <fieldset>
            <legend>" . esc_html__('Configuration Wizard', 'read-more-login') . "</legend>
            <div id='rml_wizard_tools_options' class='metabox-prefs'>
            <div><input type='hidden' name='wp_screen_options[option]' value='rml_show_wizard' /></div>
            <div><input type='hidden' name='wp_screen_options[value]' value='yes' /></div>
            <div class='cmi_custom_fields'>
                <label for='rml_show_config_wizard'><input " . $show_config_wizard_checked . " type='checkbox' value=1 name='rml_wizard[".WizardOptions::SHOW_CONFIG_WIZARD."]' id='rml_show_config_wizard' /> " . esc_html__('Show Configuration Wizard','read-more-login') . " </label>
            </div>
            </div>
            </fieldset>
            <p class='submit'>
            $button
            </p>";
        }
        return $return;
    }

    static public function SetScreenOptions($status, $option)
    {
        if ($option === 'rml_show_wizard')
        {
            $wizard_options = new WizardOptions();
            /* Checkbox don't send any values if unchecked. Must first clear model data manually. */
            $wizard_options->SetData(WizardOptions::SHOW_CONFIG_WIZARD, 0);

            if(isset($_POST['rml_wizard']))
            {
                $values = $_POST['rml_wizard'];
                if (isset($values))
                {
                    foreach ($values as $key => $value)
                    {
                        $wizard_options->SetData($key, $value);
                    }
                }
            }

            $wizard_options->SaveData();
        }

        /* Return the status argument, which indicate WordPress shall not handle this. We are handling it ourselves. */
        return $status;
    }

    static public function AdminPluginInfoPage()
    {
        echo '<div class="wrap">';

        /* translators: Name of the plugin, should only be translated if done consistently. */
        echo '<h2>' . esc_html__('Read More Login', 'read-more-login') . ' - ' . get_admin_page_title() . '</h2>';

        echo '<hr>';

        $wizard_options = get_option(WizardOptions::OPTION_NAME);
        if($wizard_options[WizardOptions::SHOW_CONFIG_WIZARD])
        {
            $wizzard_controller = new WizardController();
            $wizard_html = $wizzard_controller->Draw();

            echo '<div id="rml_wizard_placeholder" class="rml_wizard_placeholder">';
            echo $wizard_html;
            echo '</div>';
        }

        echo '<h3>' . esc_html__('Introduction', 'read-more-login') . '</h3>';
        echo '<p>' . esc_html__('Puts a combined read-more, log-in and registration form inside in posts and pages. Visitors must log in or register to read more. Remaining text will be protected and hidden for other visitors.', 'read-more-login') . '</p>';
        echo '<p>' . sprintf(esc_html__('Live demo and list of features: %s', 'read-more-login'), '<a href="https://www.readmorelogin.com/live-demo/" target="_blank">www.readmorelogin.com/live-demo</a>') . '</p>';

        echo '<h3>' . esc_html__('Add shortcodes to your posts and pages', 'read-more-login') . '</h3>';
        /* translators: %s: Shortcode. */
        echo '<p>' . sprintf(esc_html__('In the posts and pages you want login form, put the shortcode %s in the text where you want the login form to appear.', 'read-more-login'), '<b>[rml_read_more]</b>') . '</p>';
        $warning = '<b>' . esc_html__('Warning!', 'read-more-login') . '</b>';
        /* translators: %1$s: The text Warning! %2$s: shortcode. */
        echo '<p>' . sprintf(esc_html__('%1$s Do not use the shortcode %2$s inside tables or other html elements.', 'read-more-login'), $warning, '<b>[rml_read_more]</b>') . '</p>';
        /* translators: %s: </table> */
        echo '<p>' . sprintf(esc_html__('When login form shows, the shortcode will cut of the remaining text including html tags for that post/page. You may miss html end tags (tags marked with slash like %s). Missing end tags may mess up your page. To be safe is recommended to put in the shortcode between two completed paragraphs.', 'read-more-login'), '&lt;/table&gt;') . '</p>';
        echo '<p>' . esc_html__('This example shows correct usage of the shortcode between two paragraphs:', 'read-more-login') . '</p>';
        echo '<img src="' . plugins_url('img/shortcode_example.png', dirname(__FILE__)) . '">';
        $html_tags = '&lt;p&gt;&lt;/p&gt;';
        /* translators: %s is <p></p> */
        echo '<p>' . sprintf(esc_html__('Note! On newer WordPress versions, the paragraph tag %s will be hidden.', 'read-more-login'), $html_tags) . '</p>';

        echo '</div>';
    }

    static public function StatisticsPage()
    {
        echo '<div class="wrap">';
        /* translators: Name of the plugin, should only be translated if done consistently. */
        echo '<h2>' . esc_html__('Read More Login', 'read-more-login') . ' - ' . get_admin_page_title() . '</h2>';
        echo '<p>' . esc_html__('Statistics for user registrations.', 'read-more-login') . '</p>';
        echo '<p>' . esc_html__('These numbers applies only for the registration forms inside articles.', 'read-more-login') . '</p>';

        $stat_data = new StatisticsDbTable();
        $count = $stat_data->LoadAllData();

        echo '<table border="1">';
        echo '<tr>';
        echo '<th>' . esc_html__('Page ID', 'read-more-login') . '</th>';
        echo '<th>' . esc_html__('Page title', 'read-more-login') . '</th>';
        echo '<th>' . esc_html__('Page view counter', 'read-more-login') . '</th>';
        echo '<th colspan="2">' . esc_html__('Registration started counter', 'read-more-login') . '</th>';
        echo '<th colspan="2">' . esc_html__('Sent e-mail counter', 'read-more-login') . '</th>';
        echo '<th colspan="2">' . esc_html__('Completed counter', 'read-more-login') . '</th>';
        echo '</tr>';

        $page_views_totals_count = 0;
        $registered_totals_count = 0;
        $email_totals_sent = 0;
        $completed_totals_count = 0;

        for ($index=0; $index<$count; $index++)
        {
            $post_id = $stat_data->GetDataIndex($index, StatisticsDbTable::POST_ID);
            $page_views_count = $stat_data->GetDataIndex($index, StatisticsDbTable::PAGE_VIEW);
            $registered_count = $stat_data->GetDataIndex($index, StatisticsDbTable::REGISTER);
            $email_sent = $stat_data->GetDataIndex($index, StatisticsDbTable::VERIFY);
            $completed_count = $stat_data->GetDataIndex($index, StatisticsDbTable::COMPLETED);

            $page_views_totals_count += $page_views_count;
            $registered_totals_count += $registered_count;
            $email_totals_sent += $email_sent;
            $completed_totals_count += $completed_count;

            if ($page_views_count == 0)
            {
                $registered_percent = '0.0%';
                $email_percent = '0.0%';
                $completed_percent = '0.0%';
            }
            else
            {
                $registered_percent = number_format (100.0 * $registered_count / $page_views_count, 1) . '%';
                $email_percent = number_format (100.0 * $email_sent / $page_views_count, 1) . '%';
                $completed_percent = number_format (100.0 * $completed_count / $page_views_count, 1) . '%';
            }

            echo '<tr>';
            echo '<td>' . $post_id . '</td>';
            echo '<td>' . get_the_title($post_id) . '</td>';
            echo '<td>' . $page_views_count . '</td>';
            echo '<td>' . $registered_count . '</td>';
            echo '<td>' . $registered_percent . '</td>';
            echo '<td>' . $email_sent . '</td>';
            echo '<td>' . $email_percent . '</td>';
            echo '<td>' . $completed_count . '</td>';
            echo '<td>' . $completed_percent . '</td>';
            echo '</tr>';
        }

        if ($page_views_totals_count == 0)
        {
            $registered_total_percent = '0.0%';
            $email_total_percent = '0.0%';
            $completed_total_percent = '0.0%';
        }
        else
        {
            $registered_total_percent = number_format (100.0 * $registered_totals_count / $page_views_totals_count, 1) . '%';
            $email_total_percent = number_format (100.0 * $email_totals_sent / $page_views_totals_count, 1) . '%';
            $completed_total_percent = number_format (100.0 * $completed_totals_count / $page_views_totals_count, 1) . '%';
        }

        echo '<tr>';
        /* translators: This is sum of adding calculation. */
        echo '<td>' . esc_html__('Totals', 'read-more-login') . '</td>';
        echo '<td></td>';
        echo '<td>' . $page_views_totals_count . '</td>';
        echo '<td>' . $registered_totals_count . '</td>';
        echo '<td>' . $registered_total_percent . '</td>';
        echo '<td>' . $email_totals_sent . '</td>';
        echo '<td>' . $email_total_percent . '</td>';
        echo '<td>' . $completed_totals_count . '</td>';
        echo '<td>' . $completed_total_percent . '</td>';
        echo '</tr>';

        echo '</table>';

        echo '</div>';
    }

    static public function StatusPage()
    {
        echo '<div class="wrap">';
        /* translators: Name of the plugin, should only be translated if done consistently. */
        echo '<h2>' . esc_html__('Read More Login', 'read-more-login') . ' - ' . get_admin_page_title() . '</h2>';
        echo '<p>' . esc_html__('Show status for e-mails sent for user registrations and password reset.', 'read-more-login');
        echo '<br>' . esc_html__('Listings in the tables will be cleared after 30 days', 'read-more-login') .'</p>';

        echo '<h3>' . esc_html__('User registration status', 'read-more-login') . '</h3>';
        echo '<p>' . esc_html__('Registration links sent to confirm e-mail addresses gets expired after 48 hours.', 'read-more-login') . '</p>';

        $reg_data = new RegistrationDbTable();
        $count = $reg_data->LoadAllData();

        echo '<table border="1">';
        echo '<tr>';
        /* translators: Database reference number for registrations. */
        echo '<th>' . esc_html__('Reg.ID', 'read-more-login') . '</th>';
        echo '<th>' . esc_html__('Time-stamp', 'read-more-login') . '</th>';
        echo '<th>' . esc_html__('Status', 'read-more-login') . '</th>';
        echo '<th>' . esc_html__('Username', 'read-more-login') . '</th>';
        echo '<th>' . esc_html__('E-mail', 'read-more-login') . '</th>';
        echo '<th>' . esc_html__('Page ID', 'read-more-login') . '</th>';
        echo '<th>' . esc_html__('Page title', 'read-more-login') . '</th>';
        echo '</tr>';
        $any=false;

        for ($index=0; $index<$count; $index++)
        {
            $state = $reg_data->GetDataIndex($index, RegistrationDbTable::STATE);

            switch ($state)
            {
                case RegistrationDbTable::STATE_REGISTRATION_STARTED:
                case RegistrationDbTable::STATE_REGISTRATION_EMAIL_SENT:
                case RegistrationDbTable::STATE_REGISTRATION_EMAIL_CONFIRMED:
                case RegistrationDbTable::STATE_REGISTRATION_MORE_INFO:
                case RegistrationDbTable::STATE_REGISTRATION_USER_CREATED:
                case RegistrationDbTable::STATE_REGISTRATION_EXPIRED:
                    $any=true;
                    echo '<tr>';
                    echo '<td>' . $reg_data->GetDataIndex($index, RegistrationDbTable::PRIMARY_KEY) . '</td>';
                    echo '<td>' . $reg_data->GetDataIndex($index, RegistrationDbTable::TIMESTAMP) . '</td>';
                    echo '<td>' . self::GetRegStatusName($state) . '</td>';
                    echo '<td>' . $reg_data->GetDataIndex($index, RegistrationDbTable::USERNAME) . '</td>';
                    echo '<td>' . $reg_data->GetDataIndex($index, RegistrationDbTable::EMAIL) . '</td>';
                    $post_id = $reg_data->GetDataIndex($index, RegistrationDbTable::POST_ID);
                    echo '<td>' . $post_id . '</td>';
                    echo '<td>' . get_the_title($post_id) . '</td>';
                    echo '</tr>';
                    break;
            }
        }
        echo '</table>';

        if(!$any)
        {
            echo '<p>No registrations.</p>';
        }

        echo '<h3>' . esc_html__('Password reset status', 'read-more-login') . '</h3>';
        echo '<p>' . esc_html__('Password reset links gets expired after 1 hour.', 'read-more-login') . '</p>';

        echo '<table border="1">';
        echo '<tr>';
        /* translators: Database reference number for registrations. */
        echo '<th>' . esc_html__('Reg.ID', 'read-more-login') . '</th>';
        echo '<th>' . esc_html__('Time-stamp', 'read-more-login') . '</th>';
        echo '<th>' . esc_html__('Status', 'read-more-login') . '</th>';
        echo '<th>' . esc_html__('Username', 'read-more-login') . '</th>';
        echo '<th>' . esc_html__('E-mail', 'read-more-login') . '</th>';
        echo '<th>' . esc_html__('Page ID', 'read-more-login') . '</th>';
        echo '<th>' . esc_html__('Page title', 'read-more-login') . '</th>';
        echo '</tr>';

        $any=false;
        for ($index=0; $index<$count; $index++)
        {
            $state = $reg_data->GetDataIndex($index, RegistrationDbTable::STATE);

            switch ($state)
            {
                case RegistrationDbTable::STATE_RESET_PASSWORD_EMAIL_SENT:
                case RegistrationDbTable::STATE_RESET_PASSWORD_EMAIL_CONFIRM:
                case RegistrationDbTable::STATE_RESET_PASSWORD_DONE:
                case RegistrationDbTable::STATE_RESET_PASSWORD_EXPIRED:
                    $any=true;
                    echo '<tr>';
                    echo '<td>' . $reg_data->GetDataIndex($index, RegistrationDbTable::PRIMARY_KEY) . '</td>';
                    echo '<td>' . $reg_data->GetDataIndex($index, RegistrationDbTable::TIMESTAMP) . '</td>';
                    echo '<td>' . self::GetRegStatusName($state) . '</td>';
                    echo '<td>' . $reg_data->GetDataIndex($index, RegistrationDbTable::USERNAME) . '</td>';
                    echo '<td>' . $reg_data->GetDataIndex($index, RegistrationDbTable::EMAIL) . '</td>';
                    $post_id = $reg_data->GetDataIndex($index, RegistrationDbTable::POST_ID);
                    echo '<td>' . $post_id . '</td>';
                    echo '<td>' . get_the_title($post_id) . '</td>';
                    echo '</tr>';
                    break;
            }
        }
        echo '</table>';

        if(!$any)
        {
            echo '<p>' . esc_html__('No registrations.', 'read-more-login') . '</p>';
        }

        echo '</div>';
    }

    static public function GetRegStatusName($reg_status)
    {
        switch($reg_status)
        {
            case RegistrationDbTable::STATE_REGISTRATION_STARTED:
                $name=esc_html__('Registering started', 'read-more-login');
                break;

            case RegistrationDbTable::STATE_REGISTRATION_EMAIL_SENT:
                $name=esc_html__('E-mail sent', 'read-more-login');
                break;

            case RegistrationDbTable::STATE_REGISTRATION_EMAIL_CONFIRMED:
                $name=esc_html__('E-mail confirmed', 'read-more-login');
                break;

            case RegistrationDbTable::STATE_REGISTRATION_MORE_INFO:
                $name=esc_html__('Request user data', 'read-more-login');
                break;

            case RegistrationDbTable::STATE_REGISTRATION_USER_CREATED:
                $name=esc_html__('Completed', 'read-more-login');
                break;

            case RegistrationDbTable::STATE_RESET_PASSWORD_EMAIL_SENT:
                $name=esc_html__('E-mail sent', 'read-more-login');
                break;

            case RegistrationDbTable::STATE_RESET_PASSWORD_EMAIL_CONFIRM:
                $name=esc_html__('E-mail confirmed', 'read-more-login');
                break;

            case RegistrationDbTable::STATE_RESET_PASSWORD_DONE:
                $name=esc_html__('Completed', 'read-more-login');
                break;

            case RegistrationDbTable::STATE_RESET_PASSWORD_EXPIRED:
                $name=esc_html__('Expired', 'read-more-login');
                break;

            case RegistrationDbTable::STATE_REGISTRATION_EXPIRED:
                $name=esc_html__('Expired', 'read-more-login');
                break;

            default:
                $name = strval($reg_status);
        }

        return $name;
    }
}