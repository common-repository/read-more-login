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

use WP_PluginFramework\HtmlElements\Fieldset;
use WP_PluginFramework\HtmlElements\Label;
use WP_PluginFramework\HtmlElements\InputCheckbox;
use WP_PluginFramework\HtmlElements\Legend;
use WP_PluginFramework\HtmlElements\Span;
use WP_PluginFramework\HtmlElements\P;

class CheckBox extends InputComponent
{
    const DefaultValueType = 'integer';

    protected $Items = array();

    /** @var string Label for checkbox, showed behind input. */
    protected $label;

    public function __construct($header=null, $checked=0, $name=null, $attributes=array())
    {
        $this->Items[$name] = $header;

        /* TODO Must change value and name to hold aan array of items.*/
        $properties['value'] = $checked;
        $properties['name'] = $name;

        $properties['input_attributes'] = array(
            'class' => ['wpf-checkbox-input']
        );

        parent::__construct($attributes, $properties);
    }

    public function SetValue($value)
    {
        if(gettype($value) == 'string')
        {
            $value = intval($value);
        }

        parent::SetValue($value);
    }

    public function SetChecked($checked)
    {
        $this->SetValue($checked);
    }

    public function GetChecked()
    {
        return $this->GetValue();
    }

    public function ClearCheckboxBeforeFormPost()
    {
        $this->SetValue(0);
    }

    public function CreateContent($config=null)
    {
        $wrapper = parent::CreateContent($config);

        foreach ($this->Items as $value => $item)
        {
            $input_attr = $this->input_attributes;
            $input_attr['name'] = $value;
            $input_attr['value'] = '1';

            if ($this->value == 1)
            {
                $input_attr['checked'] = 'checked';
            }

            $input = new InputCheckbox($input_attr);

            $span = New Span($this->label);
            $legend = New Legend($span, array('class' => 'screen-reader-text'));

            $fieldset = New Fieldset($legend);

            $label = New Label($input, array('for' => $value));
            $label->AddContent($item);

            $fieldset->AddContent($label);

            $wrapper->AddContent($fieldset);
        }


        if(isset($this->description))
        {
            $description = new P($this->description, array('class' => 'description'));
            $wrapper->AddContent($description);
        }
    }
}
