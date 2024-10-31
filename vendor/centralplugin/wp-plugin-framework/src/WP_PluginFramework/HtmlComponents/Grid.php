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

defined( 'ABSPATH' ) || exit;

use WP_PluginFramework\HtmlElements\Div;

class Grid extends HtmlBaseComponent
{
    const Vertical = 'vertical';
    const Horizontal = 'horizontal';

    const TABLE_LAYOUT = 'table_layout';

    protected $Direction = null;

    public function __construct($direction, $size=null, $properties=null, $attributes=null)
    {
        $this->Direction = $direction;

        if($size)
        {
            for ($i = 0; $i < $size; $i++)
            {
                $div = new Div();
                $this->contents[] = $div;
            }
        }

        parent::__construct($attributes, $properties, null, 'div', true);
    }

    public function SetGrid($direction, $size=null, $properties=null, $attributes=null)
    {
        $grid = new Grid($direction, $size, $properties, $attributes);
        $this->AddContent($grid);
        return $grid;
    }

    public function AddPlaceholder($tag)
    {
        $div = new Div();
        $div->SetProperty('placeholder', $tag);
        parent::AddContent($div);
    }

    public function AddComponent($component)
    {
        $div = new Div();
        $div->SetProperty('component', $component);
        parent::AddContent($div);
    }

    public function AddContent($content)
    {
        if (!($content instanceof  Grid))
        {
            $div = new Div($content);
            parent::AddContent($div);
        }
        else
        {
            parent::AddContent($content);
        }
    }
}
