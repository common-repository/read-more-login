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
use WP_PluginFramework\HtmlComponents\DropDownList;
use WP_PluginFramework\HtmlComponents\NumberBox;
use WP_PluginFramework\HtmlComponents\RadioButtonList;
use WP_PluginFramework\HtmlComponents\TextBox;
use WP_PluginFramework\HtmlComponents\ColorPicker;
use WP_PluginFramework\HtmlComponents\PushButton;

class AdminStyleView extends AdminFormPreView
{
    /** @var CheckBox */
    public $Header1;
    /** @var NumberBox */
    public $fade_enable;
    /** @var ColorPicker */
    public $fading_height;
    /** @var ColorPicker */
    public $fade_background_color;
    /** @var ColorPicker */
    public $frame_background_color;
    /** @var NumberBox */
    public $frame_border_size;
    /** @var ColorPicker */
    public $frame_border_color;
    /** @var ColorPicker */
    public $button_color;
    /** @var ColorPicker */
    public $button_text_color;
    /** @var ColorPicker */
    public $button_border_color;
    /** @var DropDownList */
    public $form_loading;
    /** @var DropDownList */
    public $text_loading;
    /** @var PushButton */
    public $StdSubmit;

    public function __construct($id, $controller)
    {
        parent::__construct($id, $controller);

        /* translators: Admin panel headline */
        $this->AddHeader('Header1', esc_html__('Style', 'read-more-login'));

        $this->AddFormInput('fade_enable', new CheckBox());
        $this->AddFormInput('fading_height', new NumberBox());
        $this->AddFormInput('fade_background_color', new ColorPicker());
        $this->AddFormInput('frame_background_color', new ColorPicker());
        $this->AddFormInput('frame_border_size', new NumberBox());
        $this->AddFormInput('frame_border_color', new ColorPicker());
        $this->AddFormInput('button_color', new ColorPicker());
        $this->AddFormInput('button_text_color', new ColorPicker());
        $this->AddFormInput('button_border_color', new ColorPicker());
        $this->AddFormInput('form_loading', new DropDownList());
        $this->AddFormInput('text_loading', new DropDownList());

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
            'name' => 'fading',
            'header' => esc_html__('Text fading', 'read-more-login'),
            'description' => esc_html__('Set fading of text above the login form. Such fading will indicate to the reader that more text exists.', 'read-more-login')
        );
        $this->AddInputFormCategory($category);

        $category = array(
            'name' => 'color',
            'header' => esc_html__('Form colors and appearance', 'read-more-login'),
            'description' => esc_html__('The colors must be set using RGB color values in hex format. These start with the number sign # followed by 6 hex digits. Leave blank to use default colors.', 'read-more-login'));
        $this->AddInputFormCategory($category);

        $category = array(
            'name' => 'animations',
            'header' => esc_html__('Animation effects', 'read-more-login'),
            'description' => esc_html__('Set form and content loading animations.', 'read-more-login'));
        $this->AddInputFormCategory($category);

        $this->fade_enable->SetProperty('label', esc_html__('Enable text fading:', 'read-more-login'));
        $items[SettingsStyleOptions::FADING_ENABLE] = esc_html__('Yes', 'read-more-login');
        $this->fade_enable->SetProperty('Items', $items);
        $this->fade_enable->SetProperty('category', 'fading');

        $this->fading_height->SetProperty('label', esc_html__('Fading height:', 'read-more-login'));
        $this->fading_height->SetProperty('description', esc_html__('Height of fading area. Number unit is "em". Recommended value between 5 - 20.', 'read-more-login'));
        $this->fading_height->SetProperty('category', 'fading');

        $this->fade_background_color->SetProperty('label', esc_html__('Background color:', 'read-more-login'));
        $this->fade_background_color->SetProperty('description', esc_html__('The plugin is not able to detect your theme\'s background color. Please enter background color using RGB color values in hex format.', 'read-more-login'));
        $this->fade_background_color->SetProperty('category', 'fading');

        $this->frame_background_color->SetProperty('label', esc_html__('Frame background color:', 'read-more-login'));
        $this->frame_background_color->SetProperty('category', 'color');

        $this->frame_border_size->SetProperty('label', esc_html__('Frame border thickness:', 'read-more-login'));
        $this->frame_border_size->SetProperty('description', esc_html__('Set border thickness in pixel. Use 0 to disable the border.', 'read-more-login'));
        $this->frame_border_size->SetProperty('category', 'color');

        $this->frame_border_color->SetProperty('label', esc_html__('Frame border color:', 'read-more-login'));
        $this->frame_border_color->SetProperty('category', 'color');

        $this->button_color->SetProperty('label', esc_html__('Button color:', 'read-more-login'));
        $this->button_color->SetProperty('category', 'color');

        $this->button_text_color->SetProperty('label', esc_html__('Button text color:', 'read-more-login'));
        $this->button_text_color->SetProperty('category', 'color');

        $this->button_border_color->SetProperty('label', esc_html__('Button border color:', 'read-more-login'));
        $this->button_border_color->SetProperty('category', 'color');

        $this->form_loading->SetProperty('label', esc_html__('Form rotations:', 'read-more-login'));
        $this->form_loading->SetProperty('category', 'animations');
        $items = array();
        $items[SettingsStyleOptions::FORM_LOADING_NO_ANIMATION] = esc_html__('No animation effects', 'read-more-login');
        /* translators: Animation effect for how new forms are loaded into a page. */
        $items[SettingsStyleOptions::FORM_LOADING_FADE] = esc_html__('Fade out and in', 'read-more-login');
        /* translators: Animation effect for how new forms are loaded into a page. */
        $items[SettingsStyleOptions::FORM_LOADING_V_ROLLING] = esc_html__('Vertical roll up and down', 'read-more-login');
        $this->form_loading->SetProperty('Items', $items);
        $this->form_loading->SetProperty('description', esc_html__('Select animations for how to forms will rotate.', 'read-more-login'));

        /* translators: The text is the hidden part of article that users must log in to read, and how this will load into the page. */
        $this->text_loading->SetProperty('label', esc_html__('Content loading:', 'read-more-login'));
        $this->text_loading->SetProperty('category', 'animations');
        $items = array();
        $items[SettingsStyleOptions::TEXT_LOADING_NO_ANIMATION] = esc_html__('No animation effects', 'read-more-login');
        /* translators: Animation effect for how text is loaded into a page. */
        $items[SettingsStyleOptions::TEXT_LOADING_FADE] = esc_html__('Fade in', 'read-more-login');
        /* translators: Animation effect for how text is loaded into a page. */
        $items[SettingsStyleOptions::TEXT_LOADING_V_ROLLING] = esc_html__('Roll down', 'read-more-login');
        /* translators: Animation effect for how text is loaded into a page. */
        $items[SettingsStyleOptions::TEXT_LOADING_RELOAD_PAGE] = esc_html__('Reload page', 'read-more-login');
        $this->text_loading->SetProperty('Items', $items);
        /* translators: The protected text is the hidden part of article that users must log in to read. */
        $this->text_loading->SetProperty('description', esc_html__('Select animations for how the protected content will load.', 'read-more-login'));

        $this->StdSubmit->SetPrimary(true);

        parent::CreateContent($parameters, $wrapper);
    }
}
