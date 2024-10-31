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
use WP_PluginFramework\HtmlComponents\PushButton;
use WP_PluginFramework\HtmlComponents\HtmlText;

class AdminLinkingView extends AdminPanelView
{
    const REGISTER_PAGE_LINK = 'RegisterPageLink';
    const LOGIN_PAGE_LINK = 'LoginPageLink';
    const PROFILE_PAGE_LINK = 'ProfilePageLink';
    const PASSWORD_PAGE_LINK = 'PasswordPageLink';
    const TERMS_PAGE = 'TermsPage';
    const LOGOUT_PAGE_REDIRECT = 'LogoutPage';

    /** @var TextLine */
    public $Header1;
    /** @var TextLine */
    public $RegisterPageLink;
    /** @var TextLine */
    public $LoginPageLink;
    /** @var TextLine */
    public $ProfilePageLink;
    /** @var TextLine */
    public $PasswordPageLink;
    /** @var TextLine */
    public $TermsPage;
    /** @var TextLine */
    public $LogoutPage;
    /** @var PushButton */
    public $StdSubmit;

    public function __construct($id, $controller)
    {
        parent::__construct($id, $controller);

        /* translators: Admin panel headline */
        $this->AddHeader('Header1', esc_html__('Links for user handling', 'read-more-login'));

        $this->AddFormInput('RegisterPageLink', new TextLine());
        $this->AddFormInput('LoginPageLink', new TextLine());
        $this->AddFormInput('ProfilePageLink', new TextLine());
        $this->AddFormInput('PasswordPageLink', new TextLine());
        $this->AddFormInput('TermsPage', new TextLine());
        $this->AddFormInput('LogoutPage', new TextLine());

        /* translators: Button label */
        $this->AddButton('StdSubmit', new PushButton(esc_html__('Save changes', 'read-more-login')));
    }

    public function CreateContent($parameters = null, $wrapper = null)
    {
        if (!isset($wrapper))
        {
            $wrapper = $this;
        }

        $this->RegisterPageLink->SetProperty('label', esc_html__('Register page:', 'read-more-login'));

        /* translators: %s will show the shortcode. */
        $msg = sprintf(esc_html__('On this page visitors can register as new users. You must put the shortcode %s on this page.', 'read-more-login'), '<b>[rml_register]</b>');
        $html_text = new HtmlText($msg);
        $this->RegisterPageLink->SetProperty('description', $html_text);

        $this->LoginPageLink->SetProperty('label', esc_html__('Login page:', 'read-more-login'));

        /* translators: %s will show the shortcode. */
        $msg = sprintf(esc_html__('On this page users can log in and out. You must put the shortcode %s on this page.', 'read-more-login'),'<b>[rml_login]</b>');
        $html_text = new HtmlText($msg);
        $this->LoginPageLink->SetProperty('description', $html_text);

        $this->ProfilePageLink->SetProperty('label', esc_html__('Profile page:', 'read-more-login'));

        /* translators: %s will show the shortcode. */
        $msg = sprintf(esc_html__('On this page users can update their personal information. You must put the shortcode %s on this page.', 'read-more-login'), '<b>[rml_profile]</b>');
        $html_text = new HtmlText($msg);
        $this->ProfilePageLink->SetProperty('description', $html_text);

        $this->PasswordPageLink->SetProperty('label', esc_html__('Change/reset password page:', 'read-more-login'));

        /* translators: %s will show the shortcode. */
        $msg = sprintf(esc_html__('On this page users can change their password. If not logged in, the user can ask for a password reset link, which will be sent by e-mail. You must put the shortcode %s on this page.', 'read-more-login'), '<b>[rml_password]</b>');
        $html_text = new HtmlText($msg);
        $this->PasswordPageLink->SetProperty('description', $html_text);

        $this->TermsPage->SetProperty('label', esc_html__('Terms and condition page:', 'read-more-login'));
        $this->TermsPage->SetProperty('description', esc_html__('On this page you can write your terms and conditions, which new users must accept to register.', 'read-more-login'));

        $this->LogoutPage->SetProperty('label', esc_html__('Logout redirect link:', 'read-more-login'));
        $this->LogoutPage->SetProperty('description', esc_html__('After user has logged out, he will be redirected to this page.', 'read-more-login'));

        $this->StdSubmit->SetPrimary(true);

        parent::CreateContent($parameters, $wrapper);
    }
}
