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

use WP_PluginFramework\PluginContainer;
use WP_PluginFramework\Views\FormView;
use WP_PluginFramework\HtmlElements\Button;
use WP_PluginFramework\HtmlElements\Form;
use WP_PluginFramework\HtmlElements\InputHidden;

class PushButton extends HtmlBaseComponent
{
    const METHOD_AJAX = 'ajax';
    const METHOD_POST = 'post';
    const METHOD_GET = 'get';

    protected $Method = self::METHOD_AJAX;
    protected $Label = null;
    protected $Primary = false;
    protected $HiddenFields = array();

    private $add_parents_hidden_field = true;

    public function __construct($label=null, $method=null, $attributes=array())
    {
        $this->Label = $label;

        if(isset($method))
        {
            $this->Method = $method;
        }

        switch($this->Method)
        {
            case self::METHOD_AJAX:
                if(!isset($attributes['class']))
                {
                    $attributes['class'] = array('wp_plugin_framework_ajax_button');
                }
                $attributes['type'] = 'button';
                break;

            case self::METHOD_POST:
                $attributes['type'] = 'submit';
                $attributes['class'] = array('wp_plugin_framework_post_button');
                break;

            case self::METHOD_GET:
                $attributes['type'] = 'get';
                $attributes['class'] = array('wp_plugin_framework_get_button');
                break;
        }

        $properties = null;

        parent::__construct($attributes, $properties);
    }

    public function SetId($id)
    {
        $this->Id = $id;

        $this->attributes['name'] = '_event';
        $this->attributes['value'] = $id;
    }

    public function CheckEventExist($event, $event_type, $event_source)
    {
        if(($event === $this->Id) and ($event_type === 'click'))
        {
            return true;
        }

        return false;
    }

    public function SetPrimary($primary)
    {
        $this->Primary = $primary;
    }

    public function GetPrimary()
    {
        return $this->Primary;
    }

    public function AddHiddenFields($name, $value)
    {
        $attributes = array(
            'name' => $name,
            'value' => $value
        );
        $this->HiddenFields[] = $attributes;
    }

    public function SetHiddenField($name, $value)
    {
        for ($i = 0; $i < count($this->HiddenFields); $i++)
        {
            if($this->HiddenFields[$i]['name'] === $name)
            {
                $this->HiddenFields[$i]['value'] = $value;
            }
        }
    }

    public function RemoveHiddenFields($name = null)
    {
        if(isset($name))
        {

        }
        else
        {
            $this->HiddenFields = array();
            $this->add_parents_hidden_field = false;
        }
    }

    public function CreateContent($config=null)
    {
        if($this->Primary)
        {
            $this->AddClass('button-primary');
        }

        if(!isset($config))
        {
            /* If no config, draw a stand-alone button with wrapped in a form */
            if($this->add_parents_hidden_field)
            {
                $hidden_fields = $this->ParentView->GetProperty('HiddenFields');
                foreach ($hidden_fields as $hidden_field_attributes)
                {
                    $this->AddHiddenFields($hidden_field_attributes['name'], $hidden_field_attributes['value']);
                }
            }

            $attributes = array();
            switch($this->Method)
            {
                case FormView::SEND_METHOD_AJAX:
                    break;

                case FormView::SEND_METHOD_POST:
                    $attributes['method'] = 'post';
                    $this->AddHiddenFields('action', PluginContainer::WP_PLUGIN_FRAMEWORK_AJAX_HANDLER);
                    $this->AddHiddenFields('_event_type', 'click');
                    break;

                case FormView::SEND_METHOD_GET:
                    $attributes['method'] = 'get';
                    $this->AddHiddenFields('action', PluginContainer::WP_PLUGIN_FRAMEWORK_AJAX_HANDLER);
                    $this->AddHiddenFields('_event_type', 'click');
                    break;
            }

            /*
            if(isset($this->FormId))
            {
                $attributes['id'] = $this->FormId;
            }
            */

            $form = new Form(null, $attributes);

            foreach($this->HiddenFields as $hidden_field_attributes)
            {
                $hidden_input = new InputHidden($hidden_field_attributes);
                $form->AddContent($hidden_input);
            }

            $button = new Button($this->Label, $this->attributes);
            $form->AddContent($button);

            $this->AddContent($form);
        }
        else
        {
            $button = new Button($this->Label, $this->attributes);
            $this->AddContent($button);
        }
    }
}
