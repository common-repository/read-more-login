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
use WP_PluginFramework\HtmlElements\H;
use WP_PluginFramework\HtmlElements\Img;
use WP_PluginFramework\Views\AdminStdView;
use WP_PluginFramework\Utils\SecurityFilter;
use WP_PluginFramework\HtmlComponents\PushButton;
use WP_PluginFramework\HtmlComponents\StatusBar;
use WP_PluginFramework\HtmlComponents\Text;
use WP_PluginFramework\HtmlElements\Table;
use WP_PluginFramework\HtmlElements\Td;
use WP_PluginFramework\HtmlElements\Tr;
use WP_PluginFramework\HtmlElements\Hr;

class WizardView extends AdminStdView
{
    /** @var StatusBar */
    public $StatusBarHeader;
    /** @var StatusBar */
    public $StatusBarFooter;
    /** @var Text */
    public $Text1;
    /** @var PushButton */
    public $PreviousButton;
    /** @var PushButton */
    public $NextButton;
    /** @var PushButton */
    public $CloseButton;

    public function __construct($id, $controller)
    {
        parent::__construct($id, $controller);

        $this->DivWrapper = array('class' => 'rml_wizard_tools');

        $this->SetMethod(self::SEND_METHOD_POST);

        $this->AddComponent('Text1', new Text());

        /* translators: Button label. */
        $this->AddComponent('PreviousButton', new PushButton('< ' . esc_html__('Previous', 'read-more-login'), PushButton::METHOD_GET));
        /* translators: Button label. */
        $this->AddComponent('CloseButton', new PushButton(esc_html__('Close', 'read-more-login'), PushButton::METHOD_AJAX));
        /* translators: Button label. */
        $this->AddComponent('NextButton', new PushButton(esc_html__('Next', 'read-more-login') . ' >', PushButton::METHOD_GET));

        $this->ShowForm = false;
    }

    public function CreateContent($parameters = null, $wrapper = null)
    {
        if(!isset($wrapper))
        {
            $wrapper = $this;
        }

        $wrapper = parent::CreateContent($parameters, $wrapper);

        $logo_url = plugins_url( 'img/icon-128x128.png', dirname(__FILE__));
        $img = new Img($logo_url);
        $td1 = new Td($img);

        $text_div = new Div(null, array('class' => 'rml_wizard_texts'));
        /* translators: Headline. */
        $header = new H(2);
        $text_div->AddContent($header);
        $this->Text1->SetHtml('<p>' . esc_html__('This configuration wizard will help you through the configurations.', ' read-more-login') . '</p>');
        $text_div->AddContent($this->Text1);
        $td2 = new Td($text_div);

        $tr = new Tr();
        $tr->AddContent($td1);
        $tr->AddContent($td2);
        $table = new Table($tr);

        $wrapper->AddContent($table);

        $wrapper->AddContent(new Hr());

        $current_page = SecurityFilter::SafeReadGetRequest('page', SecurityFilter::STRING_KEY_NAME);

        $button_table = new Table();

        switch($current_page)
        {
            case 'rml-admin-menu':
                $current_tab = SecurityFilter::SafeReadGetRequest('tab', SecurityFilter::STRING_KEY_NAME);
                if(!isset($current_tab))
                {
                    $current_tab = 'forms';
                }
                switch($current_tab)
                {
                    case 'forms':
                        $text = '<p>' . esc_html__('The Configuration Wizard will help you with the setup of this plugin.','read-more-login') . '</p>';
                        $text .= '<p>' . esc_html__('On this page you can adjust the text in the forms and select what information will be required from new users during registrations. The preview will show you how it will look like in real.','read-more-login') . '</p>';
                        $text .= '<p>' . esc_html__('The messages can contain HTML elements and variables. The variables will transform into user and site related texts. At the bottom of this page you will find a list of defined variables that can be used.','read-more-login') . '</p>';
                        $this->Text1->SetHtml($text);

                        $this->NextButton->RemoveHiddenFields();
                        $this->NextButton->AddHiddenFields('page', 'rml-admin-menu');
                        $this->NextButton->AddHiddenFields('tab', 'style');

                        $this->PreviousButton->SetVisibility(false);
                        $this->NextButton->SetPrimary(true);

                        $td1 = new Td($this->PreviousButton);
                        $td2 = new Td($this->CloseButton);
                        $td3 = new Td($this->NextButton);
                        $tr = new Tr($td1);
                        $tr->AddContent($td2);
                        $tr->AddContent($td3);
                        $button_table->AddContent($tr);
                        $step = 1;
                        break;

                    case 'style':
                        $text = '<p>' . esc_html__('On this page you can adjust the style, colors and configure animation effects for the forms.','read-more-login') . '</p>';
                        $color_picker_link = '<a href="https://www.w3schools.com/colors/colors_picker.asp" target="_blank">HTML Color Picker</a>';
                        /* translators: %s: Link to an online color picker. */
                        $text .= '<p>' . sprintf(esc_html__('The colors must be manually entered using RGB color values in hex format. If you need a color picker, this one is recommended: %s','read-more-login'), $color_picker_link) . '</p>';
                        $text .= '<p>' . esc_html__('If you have a colored background behind the article, you must specify this color in the Background color input field below.','read-more-login') . '</p>';
                        $this->Text1->SetHtml($text);

                        $this->PreviousButton->RemoveHiddenFields();
                        $this->PreviousButton->AddHiddenFields('page', 'rml-admin-menu');
                        $this->PreviousButton->AddHiddenFields('tab', 'forms');

                        $this->NextButton->RemoveHiddenFields();
                        $this->NextButton->AddHiddenFields('page', 'rml-admin-menu');
                        $this->NextButton->AddHiddenFields('tab', 'email');

                        $this->NextButton->SetPrimary(true);

                        $td1 = new Td($this->PreviousButton);
                        $td2 = new Td($this->CloseButton);
                        $td3 = new Td($this->NextButton);
                        $tr = new Tr($td1);
                        $tr->AddContent($td2);
                        $tr->AddContent($td3);
                        $button_table->AddContent($tr);
                        $step = 2;
                        break;

                    case 'email':
                        $text = '<p>' . esc_html__('These e-mails are sent when somebody registers or has requested a password reset.','read-more-login') . '</p>';
                        $text .= '<p>' . esc_html__('','read-more-login') . '</p>';
                        $text .= '<p>' . esc_html__('The messages can contain HTML elements and variables. The variables will transform into user and site related texts. At the bottom of this page you will find a list of defined variables that can be used.','read-more-login') . '</p>';
                        $this->Text1->SetHtml($text);

                        $this->PreviousButton->RemoveHiddenFields();
                        $this->PreviousButton->AddHiddenFields('page', 'rml-admin-menu');
                        $this->PreviousButton->AddHiddenFields('tab', 'style');

                        $this->NextButton->RemoveHiddenFields();
                        $this->NextButton->AddHiddenFields('page', 'rml-admin-menu');
                        $this->NextButton->AddHiddenFields('tab', 'linking');

                        $this->NextButton->SetPrimary(true);

                        $td1 = new Td($this->PreviousButton);
                        $td2 = new Td($this->CloseButton);
                        $td3 = new Td($this->NextButton);
                        $tr = new Tr($td1);
                        $tr->AddContent($td2);
                        $tr->AddContent($td3);
                        $button_table->AddContent($tr);
                        $step = 3;
                        break;

                    case 'linking':
                        $add_page_link = admin_url() . 'post-new.php?post_type=page';
	                    /* translators: This is label to a link for adding new pages. */
                        $add_page = '<a href="' . $add_page_link. ' ">' . esc_html__('add new page', 'read-more-login') . '</a>';
                        /* translators: %s: Link to add new pages in the dashboard. */
                        $text = '<p>' . sprintf(esc_html__('Make links to pages for user handling like login, registering, profile and password recovery pages. You must manually created these pages by %s.','read-more-login'), $add_page) . '</p>';
                        $text .= '<p>' . esc_html__('The pages must contain shortcodes that will display the forms. You must write the shortcodes as given below into these pages. The shortcode must be surrounded by brackets.','read-more-login') . '</p>';
                        $this->Text1->SetHtml($text);

                        $this->PreviousButton->RemoveHiddenFields();
                        $this->PreviousButton->AddHiddenFields('page', 'rml-admin-menu');
                        $this->PreviousButton->AddHiddenFields('tab', 'email');

                        $this->NextButton->RemoveHiddenFields();
                        $this->NextButton->AddHiddenFields('page', 'rml-admin-help');

                        $this->NextButton->SetPrimary(true);

                        $td1 = new Td($this->PreviousButton);
                        $td2 = new Td($this->CloseButton);
                        $td3 = new Td($this->NextButton);
                        $tr = new Tr($td1);
                        $tr->AddContent($td2);
                        $tr->AddContent($td3);
                        $button_table->AddContent($tr);
                        $step = 4;
                        break;

                    default:
                        $this->SetContent(null);
                        return;
                        break;
                }
                break;

            case 'rml-admin-help';
                $text = '<p>' . esc_html__('A basic set of configuration should now be completed. You can now start create some login forms in your posts and pages.','read-more-login') . '</p>';
                /* translators: %s: shortcode */
                $text .= '<p>' . sprintf(esc_html__('The login forms are created by writing the shortcode %s in posts and pages.','read-more-login'), '<b>[rml_read_more]</b>') . '</p>';
                $text .= '<p>' . sprintf(esc_html__('After you have put in a shortcode, you need to log out to see it in action.','read-more-login'), '<b>[rml_read_more]</b>') . '</p>';
                $this->Text1->SetHtml($text);

                $this->PreviousButton->RemoveHiddenFields();
                $this->PreviousButton->AddHiddenFields('page', 'rml-admin-menu');
                $this->PreviousButton->AddHiddenFields('tab', 'linking');

                $this->NextButton->SetVisibility(false);
                $this->CloseButton->SetPrimary(true);

                $td1 = new Td($this->PreviousButton);
                $td2 = new Td($this->CloseButton);
                $td3 = new Td($this->NextButton);
                $tr = new Tr($td1);
                $tr->AddContent($td2);
                $tr->AddContent($td3);
                $button_table->AddContent($tr);
                $step = 5;
                break;

            default:
                $this->SetContent(null);
                return;
                break;

        }

        /* translators: %1$d: Step number for configuration wizard. %2$d: Total number of wizard steps. */
        $text = sprintf(esc_html__('Configuration Wizard - Step %1$d of %2$d','read-more-login'), $step,5);
        $header->SetContent($text);

        $button_div_attr = array('class' => 'rml_wizard_buttons');
        $button_div = new Div($button_table, $button_div_attr);
        $wrapper->AddContent($button_div);
    }
}
