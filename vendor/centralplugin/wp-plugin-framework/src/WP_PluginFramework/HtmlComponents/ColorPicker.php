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
use WP_PluginFramework\HtmlElements\InputText;
use WP_PluginFramework\HtmlElements\P;

class ColorPicker extends InputComponent
{
    const DefaultValueType = 'string';

    protected $input_attributes = array();

    public function __construct($label=null, $text=null, $name=null, $properties=array())
    {
        if(!isset($text))
        {
            $text = '';
        }

        $attributes=array();

        $properties['label'] = $label;
        $properties['value'] = $text;
        $properties['name'] = $name;

        parent::__construct($attributes, $properties);
    }

    public function SetValue($value)
    {
        parent::SetProperty('value', $value);
        $this->touched['value'] = true;
    }

    public function GetValue()
    {
        return parent::GetProperty('value');
    }

    public function SetText($text)
    {
        $this->SetValue($text);
    }

    public function GetText()
    {
        return $this->GetValue();
    }

    public function CreateContent($config=null)
    {
        $wrapper = parent::CreateContent($config);

        $input_attr = $this->input_attributes;
        $input_attr['name'] = $this->name;
        $input_attr['value'] = $this->value;

        if(isset($this->type))
        {
            $input_attr['type'] = $this->type;
        }

        $input = new InputText($input_attr);

        $div_color_show_attr = array();
        $div_color_show_attr['class'] = 'wpf-color-show';
        $div_color_show_attr['style'] = 'background-color:' . $this->value . ';';

        $color_show = new Div('&nbsp;', $div_color_show_attr);

        $div_color_picker_attr = array();
        $div_color_picker_attr['class'] = 'wpf-color-picker';

        $color_picker = new Div($input, $div_color_picker_attr);
        $color_picker->AddContent($color_show);

        $wrapper->AddContent($color_picker);

        if(isset($this->description))
        {
            $description = new P($this->description, array('class' => 'description'));
            $wrapper->AddContent($description);
        }
    }
}
