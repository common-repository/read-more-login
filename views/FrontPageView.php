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

use WP_PluginFramework\PluginContainer;
use WP_PluginFramework\Views\StdView;
use WP_PluginFramework\HtmlComponents\HtmlBaseComponent;
use WP_PluginFramework\HtmlComponents\HtmlText;
use WP_PluginFramework\HtmlComponents\CheckBox;

class FrontPageView extends StdView
{
    /**
     * Add class attributes to components.
     *
     * @param $id @var StatusBar
     * @param $component HtmlBaseComponent
     * @param null $properties
     */
    protected function AddComponent($id, $component, $properties=null)
    {
        if (is_object($component))
        {
            $class = get_class($component);
            $plugin_container = PluginContainer::Instance();
            $wp_framework_namespace = $plugin_container->GetWpFrameworkNamespace();
            switch ($class)
            {
                case $wp_framework_namespace.'\HtmlComponents\PushButton':
                    $style_options = get_option(SettingsStyleOptions::OPTION_NAME);

                    if($style_options[SettingsStyleOptions::BUTTON_COLOR])
                    {
                        $style = 'background-color:' . $style_options[SettingsStyleOptions::BUTTON_COLOR] . ';';
                        $component->SetPropertyKeyValues('attributes', 'style', $style);
                    }
                    if($style_options[SettingsStyleOptions::BUTTON_TEXT_COLOR])
                    {
                        $style = 'color:' . $style_options[SettingsStyleOptions::BUTTON_TEXT_COLOR] . ';';
                        $component->SetPropertyKeyValues('attributes', 'style', $style);
                    }
                    if($style_options[SettingsStyleOptions::BUTTON_BORDER_COLOR])
                    {
                        $style = 'border:1px solid ' . $style_options[SettingsStyleOptions::BUTTON_BORDER_COLOR] . ';';
                        $component->SetPropertyKeyValues('attributes', 'style', $style);
                    }
                    break;
            }
        }

        parent::AddComponent($id, $component);
    }

    protected function MakeTermsCheckbox()
    {
        $links_options = new SettingsLinkingOptions();
        $terms_link = $links_options->GetCompleteLinkUrl(SettingsLinkingOptions::TERMS_PAGE);

        /* translators: "terms and condition" link label used by "Yes, I accept the %s" */
        $terms_name = esc_html__('terms and condition', 'read-more-login');
        $terms_href = '<a href="' . $terms_link . '" target="_blank">' . $terms_name . ' </a>';
        /* translators: %s: link label saying terms and condition. */
        $html_text = sprintf( esc_html__('Yes, I accept the %s.', 'read-more-login' ), $terms_href);

        $html_content = new HtmlText($html_text);
        $checkbox = new CheckBox($html_content,0, 'terms');
        return $checkbox;
    }
}
