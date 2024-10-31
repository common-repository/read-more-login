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

use WP_PluginFramework\HtmlElements\Select;
use WP_PluginFramework\HtmlElements\Option;
use WP_PluginFramework\HtmlElements\P;

defined( 'ABSPATH' ) || exit;

class DropDownList extends InputComponent
{
    const DefaultValueType = 'string';

    protected $Items = array();
    //protected $Selected = array();
    protected $name = null;

    public function __construct($items=null, $selected=null, $name=null, $attributes=null)
    {
        $this->Items = $items;
        $this->value = $selected;
        $this->name = $name;

        $attributes['name'] = $name;

        parent::__construct($attributes, null);
    }

    public function CreateContent($config = null)
    {
        $wrapper = parent::CreateContent($config);

        $input_attr['name'] = $this->name;
        $select = new Select(null, $input_attr);

        foreach ($this->Items as $value => $item)
        {
            $attributes = array();

            $attributes['value'] = $value;

            if($value == $this->value)
            {
                $attributes['selected'] = 'selected';
            }

            $option = new Option($item, $attributes);

            $select->AddContent($option);
        }

        $wrapper->AddContent($select);

        if(isset($this->description))
        {
            $description = new P($this->description, array('class' => 'description'));
            $wrapper->AddContent($description);
        }
    }
}
