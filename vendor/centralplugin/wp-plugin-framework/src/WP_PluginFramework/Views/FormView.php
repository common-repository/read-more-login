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

namespace WP_PluginFramework\Views;

defined( 'ABSPATH' ) || exit;

use WP_PluginFramework\HtmlComponents\HtmlBaseComponent;
use WP_PluginFramework\PluginContainer;
use WP_PluginFramework\HtmlElements\Form;
use WP_PluginFramework\HtmlElements\InputHidden;
use WP_PluginFramework\HtmlElements\Table;
use WP_PluginFramework\HtmlElements\Tbody;
use WP_PluginFramework\HtmlElements\Tr;
use WP_PluginFramework\HtmlElements\Td;
use WP_PluginFramework\HtmlElements\P;
use WP_PluginFramework\HtmlElements\H;
use WP_PluginFramework\HtmlElements\Hr;
use WP_PluginFramework\Utils\DebugLogger;

class FormView extends View
{
    protected $FormId = null;
    /** @var array $FormInputs */
    protected $FormInputs = array();
    protected $Buttons = array();
    protected $InputFormCategories = array();
    protected $ShowForm = true;

    const SEND_METHOD_AJAX = 'ajax';
    const SEND_METHOD_POST = 'post';
    const SEND_METHOD_GET = 'get';

    private $Method = 'ajax';

    protected $HiddenFields = array();
    protected $ContentConfig = array();
    protected $FormTableTrWrapper = null;

    public function __construct($controller, $attributes=array())
    {
        parent::__construct($controller, null, $attributes);

        $this->ContentConfig['form_input_layout'] = 'single_column_table';
        $this->ContentConfig['form_placeholder_table_attr'] = array('class' => 'wpf-table-placeholder');
        $this->ContentConfig['form_placeholder_tbody_attr'] = array('class' => 'wpf-table-placeholder');
        $this->ContentConfig['form_placeholder_tr_attr']  = array('class' => 'wpf-table-placeholder');
        $this->ContentConfig['form_placeholder_th_attr'] = array('class' => 'wpf-table-placeholder-input');
        $this->ContentConfig['form_placeholder_td_attr'] = array('class' => 'wpf-table-placeholder-input');

        $my_class = get_called_class();
    }

    public function SetMethod($method)
    {
        $this->Method = $method;
    }

    /**
     * @param $id
     * @param $component HtmlBaseComponent
     */
    public function AddFormInput($id, $component)
    {
        $this->AddComponent($id, $component);
        $this->FormInputs[$id] = $component;

        /* If no name is set for a form input, use the id as name*/
        if($this->FormInputs[$id]->GetProperty('name') == null)
        {
            $this->FormInputs[$id]->SetProperty('name', $id);
        }

        $component->SetFormId($this->FormId);
    }

    public Function AddInputFormCategory($category)
    {
        $this->InputFormCategories[] = $category;
    }

    public function AddButton($id, $component)
    {
        $this->AddComponent($id, $component);
        $this->Buttons[$id] = $component;
    }

    public function AddHiddenFields($name, $value)
    {
        $attributes = array(
            'name' => $name,
            'value' => $value
        );
        $this->HiddenFields[] = $attributes;
    }

    public function GetFormInputComponent($name=null)
    {
        if(!isset($name))
        {
           return $this->FormInputs;
        }
        else
        {
            foreach ($this->FormInputs as $form_input)
            {
                if ($name === $form_input->GetProperty('name'))
                {
                    return $form_input;
                }
            }
        }

        return null;
    }

    public function ClearCheckboxBeforeFormPost()
    {
        foreach($this->FormInputs as $component)
        {
            $component->ClearCheckboxBeforeFormPost();
        }
    }

    /**
     * @param array $inputs string List of component names
     */
    public function ShowInputErrorIndications($inputs=null)
    {
        if(isset($inputs))
        {
            foreach($inputs as $component_name => $value)
            {
                if(isset($this->FormInputs[$component_name]))
                {
                    $this->FormInputs[$component_name]->ShowInputErrorIndication();
                }
                else
                {
                    DebugLogger::WriteDebugError('Non-existing Html Component ' . $component_name);
                }
            }
        }
        else
        {
            foreach($this->FormInputs as $component)
            {
                $component->ShowInputErrorIndication();
            }
        }
    }

    public function HideInputErrorIndications($inputs=null)
    {
        if(isset($inputs))
        {
            foreach($inputs as $component_name => $value)
            {
                if(isset($this->FormInputs[$component_name]))
                {
                    $this->FormInputs[$component_name]->HideInputErrorIndication();
                }
                else
                {
                    DebugLogger::WriteDebugError('Non-existing Html Component ' . $component_name);
                }
            }
        }
        else
        {
            foreach($this->FormInputs as $component)
            {
                $component->HideInputErrorIndication();
            }
        }
    }

    public function SetValues($values)
    {
        if(isset($values))
        {
            foreach ($values as $key => $value)
            {
                foreach ($this->FormInputs as $component)
                {
                    /* TODO optimize this */
                    $name = $component->GetName();

                    if ($key == $name)
                    {
                        $component->SetValue($value);
                    }
                }
            }
        }
    }

    public function GetValues()
    {
        $values = array();

        foreach ($this->FormInputs as $component)
        {
            $name = $component->GetName();
            if(isset($name))
            {
                $values[$name] = $component->GetValue();
            }
        }

        return $values;
    }

    public function CreateContent($parameters=null, $wrapper=null)
    {
        if(!isset($wrapper))
        {
            $wrapper = $this;
        }

        $wrapper = parent::CreateContent($parameters, $wrapper);
        if($this->ShowForm)
        {
            $attributes = array();
            switch($this->Method)
            {
                case self::SEND_METHOD_AJAX:
                    break;

                case self::SEND_METHOD_POST:
                    $attributes['method'] = 'post';
                    $this->AddHiddenFields('action', PluginContainer::WP_PLUGIN_FRAMEWORK_AJAX_HANDLER);
                    $this->AddHiddenFields('_event_type', 'click');
                    break;

                case self::SEND_METHOD_GET:
                    $attributes['method'] = 'get';
                    $this->AddHiddenFields('action', PluginContainer::WP_PLUGIN_FRAMEWORK_AJAX_HANDLER);
                    $this->AddHiddenFields('_event_type', 'click');
                    break;
            }

            if(isset($this->FormId))
            {
                $attributes['id'] = $this->FormId;
            }

            $form = new Form(null, $attributes);
            $wrapper->AddContent($form);

            foreach($this->HiddenFields as $hidden_field_attributes)
            {
                $hidden_input = new InputHidden($hidden_field_attributes);
                $form->AddContent($hidden_input);
            }

            if (isset($this->ContentConfig['form_input_encapsulation']) and ($this->ContentConfig['form_input_encapsulation']) == 'table')
            {
                $td_form_attr = $this->ContentConfig['form_placeholder_td_attr'];
                $td_form_attr['width'] = $this->ContentConfig['form_input_width'];

                $td_form = new Td(null, $td_form_attr);
                $td_spacing = new Td(null, $this->ContentConfig['form_placeholder_td_attr']);
                $this->FormTableTrWrapper = new Tr($td_form, $this->ContentConfig['form_placeholder_tr_attr']);
                $this->FormTableTrWrapper->AddContent($td_spacing);
                $tbody_wrapper = new Tbody($this->FormTableTrWrapper, $this->ContentConfig['form_placeholder_tbody_attr']);
                $table_wrapper = new Table($tbody_wrapper, $this->ContentConfig['form_placeholder_table_attr']);
                $form->AddContent($table_wrapper);
            }
            else
            {
                $td_form = $form;
            }

            if (isset($parameters))
            {
                foreach ($this->FormInputs as $component)
                {
                    if (isset($component))
                    {
                        if (isset($component->name))
                        {
                            $name = $component->name;
                            if (isset($parameters[$name]))
                            {
                                $component->SetValue($parameters[$name]);
                            }
                        }
                    }
                }
            }

            if (empty($this->InputFormCategories))
            {
                $tbody = new Tbody(null, $this->ContentConfig['form_placeholder_tbody_attr']);
                foreach ($this->FormInputs as $component)
                {
                    if (isset($component))
                    {
                        $tbody->AddContent($component, $this->ContentConfig);
                    }
                }
                $table = new Table($tbody, $this->ContentConfig['form_placeholder_table_attr']);
                $td_form->AddContent($table);

                if (isset($this->Buttons))
                {
                    $first_button = true;
                    $button_paragraph = new P(null, array('class' => 'wpf-table-placeholder submit'));
                    foreach ($this->Buttons as $button)
                    {
                        if (isset($button))
                        {
                            if (!$first_button)
                            {
                                $button_paragraph->AddContent('&nbsp;&nbsp;');
                            }
                            $first_button = false;

                            $button_paragraph->AddContent($button, $this->ContentConfig);
                        }
                    }
                    $td_form->AddContent($button_paragraph);
                }
            }
            else
            {
                $first_category = true;
                foreach ($this->InputFormCategories as $category)
                {
                    if ($first_category == false)
                    {
                        $divider = new Hr();
                        $td_form->AddContent($divider);
                    }
                    $first_category = false;

                    $header = new H(2, $category['header']);
                    $td_form->AddContent($header);
                    if (isset($category['description']))
                    {
                        $description = new P($category['description']);
                        $td_form->AddContent($description);
                    }

                    $tbody = new Tbody(null, $this->ContentConfig['form_placeholder_tbody_attr']);
                    foreach ($this->FormInputs as $component)
                    {
                        if (isset($component))
                        {
                            if (isset($component->category) and ($component->category == $category['name']))
                            {
                                $tbody->AddContent($component, $this->ContentConfig);
                            }
                        }
                    }
                    $table = new Table($tbody, $this->ContentConfig['form_placeholder_table_attr']);
                    $td_form->AddContent($table);

                    if (isset($this->Buttons))
                    {
                        $first_button = true;
                        $button_paragraph = new P(null, array('class' => 'submit'));
                        foreach ($this->Buttons as $button)
                        {
                            if (isset($button))
                            {
                                if (!$first_button)
                                {
                                    $button_paragraph->AddContent('&nbsp;&nbsp;');
                                }
                                $first_button = false;

                                $button_paragraph->AddContent($button, $this->ContentConfig);
                            }
                        }
                        $td_form->AddContent($button_paragraph);
                    }
                }
            }
        }

        return $wrapper;
    }
}
