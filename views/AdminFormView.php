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

use WP_PluginFramework\HtmlComponents\TextLine;
use WP_PluginFramework\HtmlComponents\TextBox;
use WP_PluginFramework\HtmlComponents\CheckBox;
use WP_PluginFramework\HtmlComponents\PushButton;
use WP_PluginFramework\HtmlElements\P;

class AdminFormView extends AdminFormPreView
{
    /** @var TextLine */
    public $login_header_text;
    /** @var TextLine */
    public $login_message_text;
    /** @var CheckBox */
    public $login_show_login_form;
    /** @var CheckBox */
    public $login_show_register_btn;
    /** @var CheckBox */
    public $login_show_register_as_link;
    /** @var CheckBox */
    public $login_show_forgotten_link;
    /** @var TextLine */
    public $register_header_text;
    /** @var TextLine */
    public $register_message_text;
    /** @var CheckBox */
    public $register_collect_first_name;
    /** @var CheckBox */
    public $register_first_name_required;
    /** @var CheckBox */
    public $register_collect_last_name;
    /** @var CheckBox */
    public $register_last_name_required;
    /** @var CheckBox */
    public $register_must_accept_terms;
    /** @var TextLine */
    public $check_email_header_text;
    /** @var TextLine */
    public $check_email_message_text;
    /** @var PushButton */
    public $StdSubmit;

    public function __construct($id, $controller)
    {
        parent::__construct($id, $controller);

        /* translators: Admin panel headline */
        $this->AddHeader('Header1', esc_html__('Form setup', 'read-more-login'));

        $this->AddFormInput('login_header_text', new TextLine());
        $this->AddFormInput('login_message_text', new TextBox());
        $this->AddFormInput('login_show_login_form', new CheckBox());
        $this->AddFormInput('login_show_register_btn', new CheckBox());
        $this->AddFormInput('login_show_register_as_link', new CheckBox());
        $this->AddFormInput('login_show_forgotten_link', new CheckBox());
        $this->AddFormInput('register_header_text', new TextLine());
        $this->AddFormInput('register_message_text', new TextBox());
        $this->AddFormInput('register_collect_first_name', new CheckBox());
        $this->AddFormInput('register_first_name_required', new CheckBox());
        $this->AddFormInput('register_collect_last_name', new CheckBox());
        $this->AddFormInput('register_last_name_required', new CheckBox());
        $this->AddFormInput('register_must_accept_terms', new CheckBox());
        $this->AddFormInput('check_email_header_text', new TextLine());
        $this->AddFormInput('check_email_message_text', new TextBox());

        /* translators: Button label */
        $this->AddButton('StdSubmit', new PushButton(esc_html__('Save changes', 'read-more-login')));
    }

    public function CreateContent($parameters=null, $wrapper=null)
    {
        if (!isset($wrapper))
        {
            $wrapper = $this;
        }

        $category = array(
            'name' => 'login_form',
            /* translators: Headline */
            'header' => esc_html__('Login form', 'read-more-login'),
            /* translators: %s: Shortcode */
            'description' => sprintf(esc_html__('The login form will ask the visitors to log in or register as a new user to read the protected content. The login form is created by putting the shortcode %s in your post and pages.', 'read-more-login'),'[rml_read_more]')
        );
        $this->AddInputFormCategory($category);

        $category = array(
            'name' => 'register_form',
            /* translators: Headline */
            'header' => esc_html__('Registration form', 'read-more-login'),
            'description' => esc_html__('The registration form will be displayed after visitors press the register button. The form asks for additional information required to register. After pressing the register button in this form, an e-mail will be sent asking user to confirm his e-mail address.', 'read-more-login')
        );
        $this->AddInputFormCategory($category);

        $category = array(
            'name' => 'check_email',
            /* translators: Headline */
            'header' => esc_html__('Check e-mail form', 'read-more-login'),
            'description' => esc_html__('After the registration e-mail has been sent, this form ask the visitor to check his e-mail. The visitor needs to confirm his e-mail address by clicking a link in the e-mail to complete the registration. This link will also send the user right back to the same page and auto-login to read the remaining article.', 'read-more-login')
        );
        $this->AddInputFormCategory($category);

        $this->login_header_text->SetProperty('label', esc_html__('Header text:', 'read-more-login'));
        $this->login_header_text->SetProperty('category', 'login_form');

        $this->login_message_text->SetProperty('label', esc_html__('Message text:', 'read-more-login'));
        $this->login_message_text->SetProperty('category', 'login_form');

        $this->login_show_login_form->SetProperty('label', esc_html__('Show login form:', 'read-more-login'));
        $items = array();
        $items[SettingsFormOptions::LOGIN_SHOW_LOGIN_FORM] = esc_html__('Yes', 'read-more-login');
        $this->login_show_login_form->SetProperty('Items', $items);
        $this->login_show_login_form->SetProperty('category', 'login_form');

        $this->login_show_register_btn->SetProperty('label', esc_html__('Enable registration in form:', 'read-more-login'));
        $items = array();
        $items[SettingsFormOptions::LOGIN_SHOW_REGISTER_BTN] = esc_html__('Yes', 'read-more-login');
        $this->login_show_register_btn->SetProperty('Items', $items);
        $this->login_show_register_btn->SetProperty('category', 'login_form');

        $this->login_show_register_as_link->SetProperty('label', esc_html__('Show register button as link:', 'read-more-login'));
        $items = array();
        $items[SettingsFormOptions::LOGIN_SHOW_REGISTER_AS_LINK] = esc_html__('Yes', 'read-more-login');
        $this->login_show_register_as_link->SetProperty('Items', $items);
        $this->login_show_register_as_link->SetProperty('category', 'login_form');

        $this->login_show_forgotten_link->SetProperty('label', esc_html__('Show forgotten password link:', 'read-more-login'));
        $items = array();
        $items[SettingsFormOptions::LOGIN_SHOW_FORGOTTEN_LINK] = esc_html__('Yes', 'read-more-login');
        $this->login_show_forgotten_link->SetProperty('Items', $items);
        $this->login_show_forgotten_link->SetProperty('category', 'login_form');

        $this->register_header_text->SetProperty('label', esc_html__('Header text:', 'read-more-login'));
        $this->register_header_text->SetProperty('category', 'register_form');

        $this->register_message_text->SetProperty('label', esc_html__('Message text:', 'read-more-login'));
        $this->register_message_text->SetProperty('category', 'register_form');

        $this->register_collect_first_name->SetProperty('label', esc_html__('Ask for first name:', 'read-more-login'));
        $items = array();
        $items[SettingsFormOptions::REGISTER_COLLECT_FIRST_NAME] = esc_html__('Ask for first name when new users are registering.', 'read-more-login');
        $this->register_collect_first_name->SetProperty('Items', $items);
        $this->register_collect_first_name->SetProperty('category', 'register_form');

        $this->register_collect_last_name->SetProperty('label', esc_html__('Ask for last name:', 'read-more-login'));
        $items = array();
        $items[SettingsFormOptions::REGISTER_COLLECT_LAST_NAME] = esc_html__('Ask for last name when new users are registering.', 'read-more-login');
        $this->register_collect_last_name->SetProperty('Items', $items);
        $this->register_collect_last_name->SetProperty('category', 'register_form');

        $this->register_first_name_required->SetProperty('label', esc_html__('First name is required:', 'read-more-login'));
        $items = array();
        $items[SettingsFormOptions::REGISTER_FIRST_NAME_REQUIRED] = esc_html__('Make first name required field. User can not complete registration without filling it in.', 'read-more-login');
        $this->register_first_name_required->SetProperty('Items', $items);
        $this->register_first_name_required->SetProperty('category', 'register_form');

        $this->register_last_name_required->SetProperty('label', esc_html__('Last name is required:', 'read-more-login'));
        $items = array();
        $items[SettingsFormOptions::REGISTER_LAST_NAME_REQUIRED] = esc_html__('Make last name required field. User can not complete registration without filling it in.', 'read-more-login');
        $this->register_last_name_required->SetProperty('Items', $items);
        $this->register_last_name_required->SetProperty('category', 'register_form');

        $this->register_must_accept_terms->SetProperty('label', esc_html__('Show terms and conditions:', 'read-more-login'));
        $items = array();
        $items[SettingsFormOptions::REGISTER_MUST_ACCEPT_TERMS] = esc_html__('Require new users to tick of a checkbox to accept the terms and condition.', 'read-more-login');
        $this->register_must_accept_terms->SetProperty('Items', $items);
        $this->register_must_accept_terms->SetProperty('category', 'register_form');

        $this->check_email_header_text->SetProperty('label', esc_html__('Header text:', 'read-more-login'));
        $this->check_email_header_text->SetProperty('category', 'check_email');

        $this->check_email_message_text->SetProperty('label', esc_html__('Message text:', 'read-more-login'));
        $this->check_email_message_text->SetProperty('category', 'check_email');

        $this->StdSubmit->SetPrimary(true);

        parent::CreateContent($parameters, $wrapper);

        /* Add some space before the table */
        $wrapper->AddContent(new P());

        $table = $this->GetVariableDescriptions(true);
        $wrapper->AddContent($table);
    }
}
