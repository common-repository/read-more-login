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

use WP_PluginFramework\HtmlElements\Br;
use WP_PluginFramework\HtmlElements\Fieldset;
use WP_PluginFramework\HtmlElements\Label;
use WP_PluginFramework\HtmlElements\Input;
use WP_PluginFramework\HtmlElements\P;

defined( 'ABSPATH' ) || exit;

class RadioButtonList extends InputComponent
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

        $fieldset = new Fieldset();

        foreach ($this->Items as $value => $item)
        {
            $attributes = array();

            $attributes['type'] = 'radio';
            $attributes['name'] = $this->name;
            $attributes['value'] = $value;

            if($value == $this->value)
            {
                $attributes['checked'] = 'checked';
            }

            $input = new Input($attributes);
            $label = new Label($input);

            $fieldset->AddContent($label);
            $fieldset->AddContent($item);

            $br = new Br();
            $fieldset->AddContent($br);
        }

        $wrapper->AddContent($fieldset);

        if(isset($this->description))
        {
            $description = new P($this->description, array('class' => 'description'));
            $wrapper->AddContent($description);
        }
    }
}
