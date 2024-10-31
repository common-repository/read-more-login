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

use WP_PluginFramework\HtmlElements\H;
use WP_PluginFramework\HtmlElements\P;

class ReadMoreView extends FrontPageView
{
    /** @var H */
    public $Header;
    /** @var P */
    public $Message;

    public function __construct($id, $controller)
    {
        parent::__construct($id, $controller);

        $style_options = get_option(SettingsStyleOptions::OPTION_NAME);

        $this->DivWrapper = [
            'class' => 'wpf-controller aru_rml_from_in_post',
            'style' => 'background-color:' . $style_options[SettingsStyleOptions::FRAME_BACKGROUND_COLOR] . ';border:' . $style_options[SettingsStyleOptions::FRAME_BORDER_SIZE] . 'px solid ' .  $style_options[SettingsStyleOptions::FRAME_BORDER_COLOR] . ';'
        ];
    }

    public function UpdateClientDom($selector, $method, $arguments=null)
    {
        $style_options = get_option(SettingsStyleOptions::OPTION_NAME);

        if($style_options[SettingsStyleOptions::FORM_LOADING] != SettingsStyleOptions::FORM_LOADING_NO_ANIMATION)
        {
            if (($selector == 'div#ARU_ReadMoreLogin_ReadMoreLoginController') and ($method == 'html'))
            {
                $arg_list['selector'] = 'div#ARU_ReadMoreLogin_ReadMoreLoginController';
                $arg_list['html'] = $arguments;
                $this->UpdateClientCallFunction($style_options[SettingsStyleOptions::FORM_LOADING], $arg_list);
                return;
            }
        }

        parent::UpdateClientDom($selector, $method, $arguments);
    }

    public function UpdateClientLoadText($selector_controller, $selector_fade_div, $selector_text_holder, $remaining_text) {
        $style_options = get_option( SettingsStyleOptions::OPTION_NAME );

        $arg_list['selector_controller']  = $selector_controller;
        $arg_list['selector_fade_div']    = $selector_fade_div;
        $arg_list['selector_text_holder'] = $selector_text_holder;
        $arg_list['remaining_text']       = $remaining_text;
        $arg_list['form_loading']         = $style_options[ SettingsStyleOptions::FORM_LOADING ];
        $arg_list['text_loading']         = $style_options[ SettingsStyleOptions::TEXT_LOADING ];

        $this->UpdateClientCallFunction( 'rml_load_remaining_text', $arg_list );
    }

    public function DrawView($parameters=null)
    {
        $this->Header->SetText($parameters['header_text']);

        if($parameters['header_text'] === '')
        {
            $this->Header->SetDisplay('none');
        }

        $html = wpautop ($parameters['message_text']);
        $this->Message->SetHtml($html);

        if($parameters['message_text'] === '')
        {
            $this->Message->SetDisplay('none');
        }

        return parent::DrawView($parameters);
    }
}
