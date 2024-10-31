<?php
/**
 * WordPress Plugin MVC Framework Library
 *
 * Copyright (C) 2018 Arild Hegvik.
 *
 * GNU LESSER GENERAL PUBLIC LICENSE (GNU LGPLv3)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace WP_PluginFramework\HtmlComponents;

use WP_PluginFramework\HtmlElements\A;

defined( 'ABSPATH' ) || exit;

class NavTabMenu extends HtmlBaseComponent
{
    protected $NavTabs = array();
    protected $Selected = array();

    public function __construct($nav_tabs=null, $selected=null, $attributes=null)
    {
        $this->NavTabs = $nav_tabs;
        $this->Selected = $selected;

        parent::__construct($attributes, null, null, 'nav', true);
    }

    public function CreateContent($config = null)
    {
        foreach ($this->NavTabs as $nav_tab)
        {
            $attributes['href'] = $nav_tab['link'];
            $attributes['class'] = 'nav-tab';
            if($this->Selected == $nav_tab['name'])
            {
                $attributes['class'] .= ' nav-tab-active';
            }
            $href = new A($nav_tab['text'], $attributes);
            $this->AddContent($href);
        }
    }
}
