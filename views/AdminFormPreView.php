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

use WP_PluginFramework\HtmlComponents\DropDownList;
use WP_PluginFramework\HtmlElements\InputHidden;
use WP_PluginFramework\Utils\SecurityFilter;
use WP_PluginFramework\HtmlElements\Div;
use WP_PluginFramework\HtmlElements\H;
use WP_PluginFramework\HtmlElements\P;
use WP_PluginFramework\HtmlElements\Button;
use WP_PluginFramework\HtmlElements\Form;

class AdminFormPreView extends AdminPanelView
{
    public function __construct($id, $controller)
    {
        parent::__construct($id, $controller);

        $preview_selected = SecurityFilter::SafeReadGetRequest('preview', SecurityFilter::STRING_KEY_NAME);
        if(!$preview_selected){
            $preview_selected = 'login_form';
        }

        $this->AddHiddenFields('preview', $preview_selected);
    }

    public function GetPreviewViewClass($preview_selected)
    {
        switch($preview_selected)
        {
            case 'registration_form':
                $view = 'ARU_ReadMoreLogin\ReadMoreRegistrationView';
                break;

            case 'check_email_form':
                $view = 'ARU_ReadMoreLogin\ReadMoreCheckEmailView';
                break;

            case 'login_form':
            default:
                $view = 'ARU_ReadMoreLogin\ReadMoreLoginView';
                break;
        }
        return $view;
    }

    public function CreatePreview($preview_selected)
    {
        $design_options = get_option(SettingsStyleOptions::OPTION_NAME);

        $inner_div_attributes = array(
            'id' => 'rml_design_studio',
            'class' => 'rml_design_studio',
            'style' => 'vertical-align: top;
                        border: 5px dashed black;
                        margin: 30px 0 0 0;
                        padding: 30px;
                        background-color: ' . $design_options[SettingsStyleOptions::FADE_BACKGROUND_COLOR] . ';'
        );

        $view = $this->GetPreviewViewClass($preview_selected);
        $preview_controller = new ReadMoreLoginController(true, $view);
        $controller_html = $preview_controller->Draw();

        $placeholder_div_attributes = array(
            'id' => 'rml_readmorelogin_placeholder',
            'style' => 'position:relative;'
        );
        $placeholder_div = new Div(null, $placeholder_div_attributes);
        $fade_div = $preview_controller->GetFadeCover();
        $placeholder_div->AddHtml($fade_div);
        $placeholder_div->AddHtml($controller_html);

        $all_text = '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam commodo ultrices lectus. Sed semper nisl nulla, sit amet consectetur arcu vulputate non. Suspendisse fermentum nisi eget magna euismod tincidunt. Proin nec nulla sit amet felis accumsan congue. Integer hendrerit ante ut turpis gravida vulputate non a tortor. Aenean nisi est, ornare ut purus vitae, sodales fringilla metus. Donec odio magna, porta vel lectus sed, ullamcorper dictum leo. Vivamus dapibus lacus a lectus sodales ullamcorper. Vestibulum ac enim enim. Nunc vehicula orci convallis mollis luctus. In luctus eleifend arcu ac facilisis. Phasellus auctor est magna, a aliquam nisl hendrerit vitae. Vestibulum placerat sem euismod arcu tempus, sagittis pulvinar diam pulvinar. Praesent sed rutrum urna.</p>';
        $free_text = $preview_controller->GetFreeContentHtmlPart($all_text);

        $inner_div = new Div(null, $inner_div_attributes);
        $inner_div->AddHtml('<h1>Lorem Ipsum</h1>');
        $inner_div->AddHtml($free_text);
        $inner_div->AddContent($placeholder_div);

        $remaining_placeholder = $preview_controller->GetProtectedContentPlaceholder();
        $inner_div->AddHtml($remaining_placeholder);

        $after_div_attr = array(
            'style' => 'min-height:100px;'
        );
        $after_div = new Div(null, $after_div_attr);
        $inner_div->AddContent($after_div);

        return $inner_div;
    }

    public function CreateContent($parameters=null, $wrapper=null)
    {
        $this->AddAdminColumns();

        parent::CreateContent($parameters, $wrapper);

        $preview_selected = SecurityFilter::SafeReadGetRequest('preview', SecurityFilter::STRING_KEY_NAME);
        if(!$preview_selected){
            $preview_selected = 'login_form';
        }

        /* translators: Admin panel headline */
        $preview_header = new H(1, esc_html__('Preview', 'read-more-login'));

        $preview_div_attr = array('class' => 'wpf-admin-area-cell');

        $preview_div = new Div($preview_header, $preview_div_attr);

        $previews = array();
        $previews['login_form'] = esc_html__('Login form', 'read-more-login');
        $previews['registration_form'] = esc_html__('Registration form', 'read-more-login');
        $previews['check_email_form'] = esc_html__('Check e-mail form', 'read-more-login');
        $select_preview = new DropDownList($previews, $preview_selected, 'preview');
        $select_preview->CreateContent();

        $button_attr['class'] = 'button';
        $button_attr['type'] = 'submit';
        $button_attr['value'] = 'Submit';
        /* translators: Button label. This button which will refresh form after settings update. */
        $refresh_button = new Button(esc_html__('Refresh','read-more-login'), $button_attr);

        $select_preview_form_attr['method'] = 'get';
        $select_preview_form = new Form($select_preview, $select_preview_form_attr);
        $select_preview_form->AddContent($refresh_button);
        $p = new P(esc_html__('You can test the forms by pressing the buttons in the the preview. The preview will not send e-mail notifications.', 'read-more-login'));
        $select_preview_form->AddContent($p);

        $get_args = SecurityFilter::SafeReadGetKeyList();
        foreach($get_args as $name)
        {
            if($name != 'preview')
            {
                $value = SecurityFilter::SafeReadGetRequest($name, SecurityFilter::STRING_KEY_NAME);

                $hidden_attr['name'] = $name;
                $hidden_attr['value'] = $value;
                $hidden = new InputHidden($hidden_attr);
                $select_preview_form->AddContent($hidden);
            }
        }
        
        $select_preview_form->CreateContent();
        $preview_div->AddContent($select_preview_form);

        $inner_div = $this->CreatePreview($preview_selected);
        $preview_div->AddContent($inner_div);

        $this->StdViewSection->AddContent($preview_div);
    }
}
