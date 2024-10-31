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

use WP_PluginFramework\HtmlElements\Label;
use WP_PluginFramework\HtmlElements\Tr;
use WP_PluginFramework\HtmlElements\Th;
use WP_PluginFramework\HtmlElements\Td;
use WP_PluginFramework\Utils\DebugLogger;

class InputComponent extends HtmlBaseComponent
{
    const DefaultValueType = null;

    /** @var string Name attribute for input element as sent to sever. */
    protected $name;
    /** @var boolean Indicates component has been touched and must update component on client side. */
    protected $touched;
    /** @var string Value as sent from client side. */
    protected $value;
    /** @var string Header text to be displayed for input. */
    protected $header;
    /** @var boolean Client side can not change input. */
    protected $readonly = false;
    /** @var array string */
    protected $input_attributes = array();

    public function SetValue($value)
    {
        if(gettype($value) == static::DefaultValueType)
        {
            $this->value = $value;
        }
        else
        {
            DebugLogger::WriteDebugNote('Wrong data type ' . gettype($value) . ' for ' . get_called_class() . ' expected ' . static::DefaultValueType);
        }
    }

    public function GetValue()
    {
        return $this->value;
    }

    public function AddInputClass($values)
    {
        if($this->SetPropertyKeyValues('input_attributes', 'class', $values))
        {
            $form_selector = $this->GetFormSelector();
            $name = $this->name;
            $selector = $form_selector . ' input[name=' . $name . ']';
            if(is_array($values))
            {
                $values = implode(' ', $values);
            }
            $this->UpdateClientDom($selector, 'addClass', $values);
            return true;
        }
        else
        {
            return false;
        }
    }

    public function RemoveInputClass($values)
    {
        if($this->RemovePropertyKeyValues('input_attributes', 'class', $values))
        {
            $form_selector = $this->GetFormSelector();
            $name = $this->name;
            $selector = $form_selector . ' input[name=' . $name . ']';
            if(is_array($values))
            {
                $values = implode(' ', $values);
            }
            $this->UpdateClientDom($selector, 'removeClass', $values);
            return true;
        }
        else
        {
            return false;
        }
    }

    public function ShowInputErrorIndication()
    {
        $this->AddInputClass('wpf-input-error');
    }

    public function HideInputErrorIndication()
    {
        $this->RemoveInputClass('wpf-input-error');
    }

    public function CreateContent($config = null)
    {
        if(isset($config['form_input_layout']) and ($config['form_input_layout'] == 'double_column_table'))
        {
            $tr = new Tr(null, $config['form_placeholder_tr_attr']);

            if (isset($this->label))
            {
                $label_attr = array('for' => $this->name);
                $label = new Label($this->label, $label_attr);
                $th = new Th($label, $config['form_placeholder_th_attr']);
            }
            else
            {
                $th = new Th(null, $config['form_placeholder_th_attr']);
            }

            $tr->AddContent($th);

            $td_wrapper = new Td(null, $config['form_placeholder_td_attr']);

            $tr->AddContent($td_wrapper);

            $this->AddContent($tr);
        }
        else
        {
            if (isset($this->label))
            {
                $label = new Label($this->label, array('class' => 'wpf-label'));

                $th = new Th($label, $config['form_placeholder_th_attr']);
                $tr = new Tr($th, $config['form_placeholder_tr_attr']);
                $this->AddContent($tr);
            }

	        $form_placeholder_td_attr = null;
	        if(isset($config['form_placeholder_td_attr']))
	        {
		        $form_placeholder_td_attr = $config['form_placeholder_td_attr'];
	        }
            $td_wrapper = new Td(null, $form_placeholder_td_attr);

	        $form_placeholder_tr_attr = null;
	        if(isset($config['form_placeholder_tr_attr']))
	        {
		        $form_placeholder_tr_attr = $config['form_placeholder_tr_attr'];
	        }
            $tr = new Tr($td_wrapper, $form_placeholder_tr_attr);
            $this->AddContent($tr);
        }

        return $td_wrapper;
    }
}
