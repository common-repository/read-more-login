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

use WP_PluginFramework\DataTypes\DataType;
use WP_PluginFramework\HtmlComponents\PushButton;
use WP_PluginFramework\HtmlComponents\StatusBar;
use WP_PluginFramework\HtmlElements\H;
use WP_PluginFramework\HtmlElements\P;
use WP_PluginFramework\Utils\DebugLogger;

class StdView extends FormView
{
    protected $Headers = null;
    protected $Footers = array();

    public function __construct($id, $controller, $attributes=array())
    {
        parent::__construct($controller, $attributes);

        $this->Id = $id;
        $this->FormId = $id;

        $this->ContentConfig['form_input_encapsulation'] = 'table';
        $this->DivWrapper = ['class' => 'wpf-controller'];
        $this->ContentConfig['form_input_width'] = '400px';
    }

    public function AddHeader($id, $component)
    {
        $this->AddComponent($id, $component);
        $this->Headers[$id] = $component;
    }

    public function AddFooter($id, $component)
    {
        $this->AddComponent($id, $component);
        $this->Footers[$id] = $component;
    }

    /**
     * @param $data_object DataType
     */
    public function AddDataObjectInput($data_object)
    {
        $id = $data_object->name;

        $html_component_class = $data_object->GetProperty(DataType::HTML_WIDGET_CLASS);

        if(isset($html_component_class))
        {
            if (!strpos($html_component_class, '\\'))
            {
                /* If only class name set, use vendor's default HtmlComponents */
                $namespace = explode('\\', __NAMESPACE__);
                $html_component_class = $namespace[0] . '\\HtmlComponents\\' . $html_component_class;
            }

            if (isset($html_component_class))
            {
                $component = new $html_component_class();
                $component->SetProperties($data_object->GetProperties());
            }
        }
        else
        {
        }

        $component->SetName($id);
        $this->AddFormInput($id, $component);
    }

    public function AddModelRecordInputs($model, $submit_button=null)
    {
        /* If model is empty, we need to initialize some data first.
           In this case data will have the default values. */
        $model->InitDataIfEmpty();

        $data_objects = $model->GetDataObjectRecord();

        foreach ($data_objects as $data_object)
        {
            $this->AddDataObjectInput($data_object);
        }

        if (isset($submit_button))
        {
            if(is_string($submit_button))
            {
                $component = new PushButton($submit_button);
            }
            else if(is_object($submit_button))
            {
                $component = $submit_button;
            }

            $this->AddButton('StdSubmit', $component);
        }

        $this->AddFooter('StatusBarFooter', new StatusBar());
    }

    public function CreateContent($parameters=null, $wrapper=null)
    {
        if(!isset($wrapper))
        {
            $wrapper = $this;
        }

        $wrapper = parent::CreateContent($parameters, $wrapper);


        if(isset($this->Headers))
        {
            foreach (array_reverse($this->Headers) as $header)
            {
                if (is_string($header))
                {
                    $header = new H(1, $header);
                    $wrapper->PrependContent($header);
                }
                else if (is_object($header))
                {
                    $wrapper->PrependContent($header, $this->ContentConfig);
                }
                else
                {
                    DebugLogger::WriteDebugError('Unhandled component type ' . gettype($header));
                }
            }
        }

        if(isset($this->Footers))
        {

            foreach ($this->Footers as $footer)
            {
                if (is_string($footer))
                {
                    $footer = new P($footer);
                    $wrapper->AddContent($footer);
                }
                else if (is_object($footer))
                {
                    $footer->CreateContent($this->ContentConfig);
                    $wrapper->AddContent($footer);
                }
                else
                {
                    DebugLogger::WriteDebugError('Unhandled component type ' . gettype($footer));
                }
            }
        }

        return $wrapper;
    }
}
