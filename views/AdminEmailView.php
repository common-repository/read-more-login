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

use WP_PluginFramework\HtmlComponents\CheckBox;
use WP_PluginFramework\HtmlComponents\TextLine;
use WP_PluginFramework\HtmlComponents\TextBox;
use WP_PluginFramework\HtmlComponents\PushButton;

class AdminEmailView extends AdminPanelView
{
    /** @var CheckBox */
    public $readmore_verify_emails;
    /** @var TextLine */
    public $readmore_register_reply_address;
    /** @var TextLine */
    public $readmore_register_subject;
    /** @var TextLine */
    public $readmore_register_body;
    /** @var CheckBox */
    public $register_verify_emails;
    /** @var TextLine */
    public $register_reply_address;
    /** @var TextLine */
    public $register_subject;
    /** @var TextLine */
    public $register_body;
    /** @var TextLine */
    public $password_reply_address;
    /** @var TextLine */
    public $password_subject;
    /** @var TextLine */
    public $password_body;
    /** @var CheckBox */
    public $notification_enable;
    /** @var TextLine */
    public $notification_receiver;
    /** @var TextLine */
    public $notification_reply_address;
    /** @var TextLine */
    public $notification_subject;
    /** @var TextLine */
    public $notification_body;
    /** @var PushButton */
    public $StdSubmit;

    public function __construct($id, $controller)
    {
        parent::__construct($id, $controller);

        /* translators: Admin panel headline */
        $this->AddHeader('Header1', esc_html__('E-mail setup', 'read-more-login'));

        //$this->AddFormInput('readmore_verify_emails', new CheckBox());
        $this->AddFormInput('readmore_register_reply_address', new TextLine());
        $this->AddFormInput('readmore_register_subject', new TextLine());
        $this->AddFormInput('readmore_register_body', new TextBox());

        //$this->AddFormInput('register_verify_emails', new CheckBox());
        $this->AddFormInput('register_reply_address', new TextLine());
        $this->AddFormInput('register_subject', new TextLine());
        $this->AddFormInput('register_body', new TextBox());

        $this->AddFormInput('password_reply_address', new TextLine());
        $this->AddFormInput('password_subject', new TextLine());
        $this->AddFormInput('password_body', new TextBox());

        $this->AddFormInput('notification_enable', new CheckBox());
        $this->AddFormInput('notification_reply_address', new TextLine());
        $this->AddFormInput('notification_receiver', new TextLine());
        $this->AddFormInput('notification_subject', new TextLine());
        $this->AddFormInput('notification_body', new TextBox());

        /* translators: Button label */
        $this->AddButton('StdSubmit', new PushButton(esc_html__('Save changes', 'read-more-login')));
    }

    public function CreateContent($parameters = null, $wrapper = null)
    {
        if (!isset($wrapper))
        {
            $wrapper = $this;
        }

        $category = array(
            'name' => 'readmore',
            'header' => esc_html__('Registering via forms put inside articles', 'read-more-login'),
            'description' => esc_html__('This e-mail will be sent to visitors registering via the forms put inside articles.', 'read-more-login').' '.
                esc_html__('The e-mail asks the visitor to confirm his e-mail address by clicking a link.', 'read-more-login').' '.
                esc_html__('This link will send the new user right back to the article and let him read the remaining protected content.', 'read-more-login')
        );
        $this->AddInputFormCategory($category);

        $category = array(
            'name' => 'verify_email',
            'header' => esc_html__('Registering via the self-service registration page', 'read-more-login'),
            'description' => esc_html__('This e-mail will be sent to visitors registering via the user handling page.', 'read-more-login').' '.
                esc_html__('The e-mail asks the visitor to confirm his e-mail address by clicking a link.', 'read-more-login').' '.
                esc_html__('This link will send the new user right back to the same user handling page to complete the registration.', 'read-more-login')
        );
        $this->AddInputFormCategory($category);

        $category = array(
            'name' => 'reset_password',
            'header' => esc_html__('Reset password', 'read-more-login'),
            'description' => esc_html__('This e-mail will be sent to user requesting a password reset link. This e-mail should also inform what the username is.', 'read-more-login')
        );
        $this->AddInputFormCategory($category);

        $category = array(
            'name' => 'notification',
            'header' => esc_html__('Administrator notification', 'read-more-login'),
            'description' => esc_html__('Send e-mail to site admin or an other person when a new user has registered.', 'read-more-login')
        );
        $this->AddInputFormCategory($category);

        /*
        $this->readmore_verify_emails->SetProperty('label', esc_html__('E-mail verification:', 'read-more-login'));
        $items = array();
        $items[SettingsEmailOptions::READMORE_VERIFY_EMAILS] = esc_html__('Send e-mail to new users at registration for verification of their e-amil address.', 'read-more-login');
        $this->readmore_verify_emails->SetProperty('Items', $items);
        $this->readmore_verify_emails->SetProperty('description', esc_html__('New users must click on a link in the e-mail. If register from within an article, the user will be sent right back to same page from the e-mail link.', 'read-more-login'));
        $this->readmore_verify_emails->SetProperty('category', 'readmore');
        */

        /* translators: E-mail from/reply address. */
        $this->readmore_register_reply_address->SetProperty('label', esc_html__('From/reply:', 'read-more-login'));
        $this->readmore_register_reply_address->SetProperty('description', esc_html__('This e-mail address will appear as the sender, which the receiver can reply to if needed. Optional field, leave blank to use WordPress default.', 'read-more-login'));
        $this->readmore_register_reply_address->SetProperty('category', 'readmore');

        /* translators: E-mail message subject. */
        $this->readmore_register_subject->SetProperty('label', esc_html__('Subject:', 'read-more-login'));
        $this->readmore_register_subject->SetProperty('category', 'readmore');

        /* translators: E-mail message body. */
        $this->readmore_register_body->SetProperty('label', esc_html__('Message:', 'read-more-login'));
        $this->readmore_register_body->SetProperty('category', 'readmore');

        /*
        $this->register_verify_emails->SetProperty('label', esc_html__('E-mail verification:', 'read-more-login'));
        $items = array();
        $items[SettingsEmailOptions::REGISTER_VERIFY_EMAILS] = esc_html__('Send e-mail to new users at registration for verification of their e-amil address.', 'read-more-login');
        $this->register_verify_emails->SetProperty('Items', $items);
        $this->register_verify_emails->SetProperty('description', esc_html__('New users must click on a link in the e-mail. If register from within an article, the user will be sent right back to same page from the e-mail link.', 'read-more-login'));
        $this->register_verify_emails->SetProperty('category', 'verify_email');
        */

        /* translators: E-mail from/reply address. */
        $this->register_reply_address->SetProperty('label', esc_html__('From/reply:', 'read-more-login'));
        $this->register_reply_address->SetProperty('description', esc_html__('This e-mail address will appear as the sender, which the receiver can reply to if needed. Optional field, leave blank to use WordPress default.', 'read-more-login'));
        $this->register_reply_address->SetProperty('category', 'verify_email');

        /* translators: E-mail message subject. */
        $this->register_subject->SetProperty('label', esc_html__('Subject:', 'read-more-login'));
        $this->register_subject->SetProperty('category', 'verify_email');

        /* translators: E-mail message body. */
        $this->register_body->SetProperty('label', esc_html__('Message:', 'read-more-login'));
        $this->register_body->SetProperty('category', 'verify_email');

        /* translators: E-mail from/reply address. */
        $this->password_reply_address->SetProperty('label', esc_html__('From/reply:', 'read-more-login'));
        $this->password_reply_address->SetProperty('description', esc_html__('This e-mail address will appear as the sender, which the receiver can reply to if needed. Optional field, leave blank to use WordPress default.', 'read-more-login'));
        $this->password_reply_address->SetProperty('category', 'reset_password');

        /* translators: E-mail message subject. */
        $this->password_subject->SetProperty('label', esc_html__('Subject:', 'read-more-login'));
        $this->password_subject->SetProperty('category', 'reset_password');

        /* translators: E-mail message body. */
        $this->password_body->SetProperty('label', esc_html__('Message:', 'read-more-login'));
        $this->password_body->SetProperty('category', 'reset_password');

        $this->notification_enable->SetProperty('label', esc_html__('Enable notification:', 'read-more-login'));
        $items = array();
        $items[SettingsEmailOptions::NOTIFICATION_ENABLE] = esc_html__('Yes', 'read-more-login');
        $this->notification_enable->SetProperty('Items', $items);
        $this->notification_enable->SetProperty('category', 'notification');

        /* translators: E-mail from/reply address. */
        $this->notification_reply_address->SetProperty('label', esc_html__('From/reply:', 'read-more-login'));
        $this->notification_reply_address->SetProperty('description', esc_html__('This e-mail address will appear as the sender, which the receiver can reply to if needed. Optional field, leave blank to use WordPress default.', 'read-more-login'));
        $this->notification_reply_address->SetProperty('category', 'notification');

        /* translators: E-mail send to address. */
        $this->notification_receiver->SetProperty('label', esc_html__('Send to:', 'read-more-login'));
        $this->notification_receiver->SetProperty('category', 'notification');

        /* translators: E-mail message subject. */
        $this->notification_subject->SetProperty('label', esc_html__('Subject:', 'read-more-login'));
        $this->notification_subject->SetProperty('category', 'notification');

        /* translators: E-mail message body. */
        $this->notification_body->SetProperty('label', esc_html__('Message:', 'read-more-login'));
        $this->notification_body->SetProperty('category', 'notification');

        $this->StdSubmit->SetPrimary(true);

        parent::CreateContent($parameters, $wrapper);

        $table = $this->GetVariableDescriptions(false);
        $wrapper->AddContent($table);
    }
}
