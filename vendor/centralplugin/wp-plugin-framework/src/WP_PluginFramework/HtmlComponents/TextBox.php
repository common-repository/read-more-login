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

use WP_PluginFramework\HtmlElements\Textarea;
use WP_PluginFramework\HtmlElements\P;

class TextBox extends InputComponent
{
    const DefaultValueType = 'string';

    public function __construct($label=null, $text=null, $name=null)
    {
        $attributes = array();

        $properties['value'] = $text;
        $properties['label'] = $label;
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

        $attributes['name'] = $this->name;
        $attributes['class'] = 'large-text';
        $attributes['type'] = 'text';
        $attributes['rows'] = '8';

        $textarea = new Textarea($this->value, $attributes);

        $wrapper->AddContent($textarea);

        if(isset($this->description))
        {
            $description = new P($this->description, array('class' => 'description'));
            $wrapper->AddContent($description);
        }
    }
}
