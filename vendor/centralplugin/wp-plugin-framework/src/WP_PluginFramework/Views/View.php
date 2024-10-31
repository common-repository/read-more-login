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
use WP_PluginFramework\HtmlElements\HtmlBaseElement;
use WP_PluginFramework\HtmlElements\Div;
use WP_PluginFramework\PluginContainer;
use WP_PluginFramework\Utils\DebugLogger;

abstract class View extends HtmlBaseElement
{
    protected $Id=null;
    protected $controller = null;
    protected $AllComponents = array();
    protected $CallBacks = array();
    protected $DivWrapper = null;

    public function __construct($controller, $content=null, $attributes=null)
    {
        parent::__construct(null, null, $content, $attributes);
        $this->controller = $controller;
    }

    public function AddDivWrapper($attributes)
    {
        $this->DivWrapper = $attributes;
    }

    public function RemoveDivWrapper()
    {
        $this->DivWrapper = null;
    }

    /**
     * @param $id
     * @param $component HtmlBaseComponent
     */
    protected function AddComponent($id, $component)
    {
        if(isset($id) and is_object($component))
        {
            $component->SetId($id);
            $component->SetControllerId($this->Id);
            $component->SetParentView($this);

            if(!isset($this->$id))
            {
                $this->$id = $component;
                $this->AllComponents[$id] = $component;
            }
            else
            {
                DebugLogger::WriteDebugError('Duplicate component Id ' . $id);
            }
        }
    }

    public function Remove()
    {
        $selector = '#' . $this->Id;
        $method = 'remove';
        $this->UpdateClientDom($selector, $method);
    }

    public function DrawView($parameters=null)
    {
        $this->CreateContent($parameters);
        return $this->DrawHtml();
    }

    public function CreateContent($parameters=null, $wrapper=null)
    {
        if(!isset($wrapper))
        {
            $wrapper = $this;
        }

        if(isset($this->DivWrapper))
        {
            $attributes = $this->DivWrapper;

            if (isset($this->Id))
            {
                $attributes['id'] = $this->Id;
            }

            $div_wrapper = new Div(null, $attributes);
            $wrapper->AddContent($div_wrapper);
            $wrapper = $div_wrapper;
        }

        return $wrapper;
    }

    public function RegisterCallBack($event)
    {
        $this->CallBacks[] = $event;
    }

    public function UpdateClientReloadPage()
    {
        $this->UpdateClientDoWindowAction('location', 'reload', true);
    }

    public function UpdateClientAddCallback($event, $arguments, $controller, $view, $wp_nonce)
    {
        if(isset($arguments))
        {
            if (!is_array($arguments))
            {
                $arguments = array($arguments);
            }
        }

        $work_item = array();
        $work_item['type'] = 'wp_framework_ajax_callback';
        $work_item['action'] = PluginContainer::WP_PLUGIN_FRAMEWORK_AJAX_HANDLER;
        $work_item['controller'] = $controller;
        $work_item['view'] = $view;
        $work_item['wpnonce'] = $wp_nonce;
        $work_item['event_type'] = 'callback';
        $work_item['event'] = $event;
        $work_item['arguments'] = $arguments;
        $work_item['selector'] = '#' . $this->Id;
        $work_items[] = $work_item;
        $this->AddAjaxResponse($work_items);
    }
    
    public function UpdateClientDoWindowAction($object, $function, $arguments=null)
    {
        $work_item = array();
        $work_item['type'] = 'do_window_action';
        $work_item['object'] = $object;
        $work_item['function'] = $function;
        $work_item['arguments'] = $arguments;
        $work_items[] = $work_item;
        $this->AddAjaxResponse($work_items);
    }

    public function UpdateClientCallFunction($js_function, $arguments)
    {
        if(isset($arguments))
        {
            if (!is_array($arguments))
            {
                $arguments = array($arguments);
            }
        }

        $work_item = array();
        $work_item['type'] = 'js_call_function';
        $work_item['function'] = $js_function;
        $work_item['arguments'] = $arguments;
        $work_items[] = $work_item;
        $this->AddAjaxResponse($work_items);
    }

    public function CheckEventExist($event, $event_type, $event_source)
    {
        foreach($this->AllComponents as $component)
        {
            if($component->CheckEventExist($event, $event_type, $event_source))
            {
                return true;
            }
        }

        if($event_type === 'callback')
        {
            foreach ($this->CallBacks as $callBack)
            {
                if ($event === $callBack)
                {
                    return true;
                }
            }
        }

        return false;
    }

    public function GetAjaxResponse()
    {
        foreach ($this->AllComponents as $component)
        {
            if (isset($component))
            {
                $work_items = $component->GetAjaxResponse();
                if(!empty($work_items))
                {
                    $this->AddAjaxResponse($work_items);
                }
            }
        }

        return $this->AjaxResponse;
    }

}
