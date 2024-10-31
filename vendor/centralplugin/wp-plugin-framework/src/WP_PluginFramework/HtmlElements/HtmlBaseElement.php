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

namespace WP_PluginFramework\HtmlElements;

defined( 'ABSPATH' ) || exit;

use WP_PluginFramework\Base\BaseObject;
use WP_PluginFramework\HtmlComponents\HtmlText;
use WP_PluginFramework\Utils\DebugLogger;
use WP_PluginFramework\Utils\SecurityFilter;

abstract class HtmlBaseElement extends BaseObject
{
    /** @var array HtmlBaseElement */
    protected $contents = array();

    protected $tag = null;
    protected $end_tag = false;
    protected $attributes = array();
    protected $AjaxResponse = array();
    protected $ControllerId = null;
    protected $FormId = null;
    protected $ParentView = null;

    public function __construct($tag, $end_tag, $content, $attributes, $properties=null)
    {
        $this->tag = $tag;
        $this->end_tag = $end_tag;
        $this->SetContent($content);
        $this->attributes = $attributes;

        parent::__construct($properties);
    }

    public function SetAttribute($key, $value)
    {
        $this->SetPropertyKeyValues('attributes', $key, $value);
    }

    public function GetAttribute($key)
    {
        return $this->GetPropertyKeyValues('attributes', $key);
    }

    public function AddClass($class)
    {
        $this->SetPropertyKeyValues('attributes', 'class', $class);
    }

    public function GetClass()
    {
        return $this->GetPropertyKeyValues('attributes', 'class');
    }

    public function GetClassString()
    {
        $classes = $this->GetPropertyKeyValues('attributes', 'class');
        return implode(' ', $classes);
    }

    public function RemoveClass($class)
    {
        $this->RemovePropertyKeyValues('attributes', 'class', $class);
    }

    public function SetVisibility($visibility)
    {
        if($visibility)
        {
            $this->SetAttribute('style', 'visibility:visible;');
        }
        else
        {
            $this->SetAttribute('style', 'visibility:hidden;');
        }
    }

    public function SetDisplay($display)
    {
        $this->SetAttribute('style', 'display:'.$display.';');
    }

    public function SetId($id)
    {
        $this->attributes['id'] = $id;
    }

    public function SetControllerId($controller_id)
    {
        $this->ControllerId = $controller_id;
    }

    public function SetParentView($parent_view)
    {
        $this->ParentView = $parent_view;
    }

    public function SetFormId($form_id)
    {
        $this->FormId = $form_id;
    }

    public function SetName($name)
    {
        $this->SetProperty('name', $name);
    }

    public function GetName()
    {
        return $this->GetProperty('name');
    }

    public function CreateContent($config=null)
    {
    }

    public function SetContent($content, $config=null)
    {
        if(is_object($content))
        {
            if (empty($content->contents))
            {
                $content->CreateContent($config);
            }
        }

        parent::SetContent($content);
    }

    public function AddContent($content, $config=null)
    {
        if(is_object($content))
        {
            if (empty($content->contents))
            {
                $content->CreateContent($config);
            }
        }

        parent::AddContent($content);
    }

    public function PrependContent($content, $config=null)
    {
        if (is_object($content))
        {
            if (!isset($content->tag) or empty($content->contents))
            {
                $content->CreateContent($config);
            }
        }

        parent::PrependContent($content);
    }

    /* This function must be overridden by checkbox elements to clear value. When client side send a post method, only
       checked checkboxes will send values. This is needed for checkboxes set checked by default, which is cleared by
       user. */
    public function ClearCheckboxBeforeFormPost()
    {
    }

    public function SetHtml($html)
    {
        $html_content = new HtmlText($html);
        $this->SetContent($html_content);
    }

    /**
     * Add html text to content. This function avoid html elements from being escaped during write.
     *
     * @param string $html
     */
    public function AddHtml($html)
    {
        $html_content = new HtmlText($html);
        $this->AddContent($html_content);
    }

    public function SetText($text)
    {
        $this->SetContent($text);
    }

    public function DrawHtml()
    {
        $html = '';

        foreach ($this->contents as $content)
        {
            $content_type = gettype($content);
            switch ($content_type)
            {
                case 'object';
                    $html .= $content->DrawHtml();
                    break;

                case 'string';
                    $html .= esc_html($content);
                    break;

                case 'integer';
                case 'double';
                    $html .= strval($content);
                    break;

                default:
                    $html .= 'Error: Undefined html content type';
                    break;
            }
        }

        if (isset($this->tag))
        {
            $start_html = '<' . $this->tag;
            if (isset($this->attributes))
            {
                foreach ($this->attributes as $attribute => $value)
                {
                    if(is_array($value))
                    {
                        switch($attribute)
                        {
                            case 'class':
                                $value = implode(' ', $value);
                                break;

                            case 'style':
                                $value = implode(';', $value);
                                break;

                            default:
                                $value = '';
                                DebugLogger::WriteDebugError('Missing array handler for ' . $attribute);
                                break;
                        }
                    }

                    $start_html .= ' ' . $attribute . '="' . esc_attr($value) . '"';
                }
            }

            if ($this->end_tag)
            {
                $html = $start_html . '>' . $html . '</' . $this->tag . '>';
            }
            else
            {
                if ($html != '')
                {
                    /* We can not have inner html when closing tag don't exist. */
                    die();
                }

                $html .= $start_html . '/>';
            }
        }

        return $html;
    }

    public function AddAjaxResponse($work_items)
    {
        if(!empty($work_items))
        {
            $this->AjaxResponse = array_merge($this->AjaxResponse, $work_items);
        }
    }

    public function GetAjaxResponse()
    {
        return $this->AjaxResponse;
    }

    public function UpdateClientDom($selector, $method, $arguments=null)
    {
        if(isset($arguments))
        {
            if (!is_array($arguments))
            {
                $arguments = array($arguments);
            }
        }

        $work_item = array();
        $work_item['type'] = 'html';
        $work_item['selector'] = $selector;
        $work_item['method'] = $method;
        $work_item['arguments'] = $arguments;
        $work_items[] = $work_item;
        $this->AddAjaxResponse($work_items);
    }

    protected function GetViewSelector()
    {
        if(isset($this->ControllerId))
        {
            return 'div#' . $this->ControllerId;
        }
        return null;
    }

    protected function GetFormSelector()
    {
        $selector = $this->GetViewSelector();
        if(!$selector)
        {
            $selector = '';
        }

        if(isset($this->FormId))
        {
            if($selector)
            {
                $selector .= ' ';
            }
            $selector .= 'form#' . $this->FormId;
        }

        return $selector;
    }

    public function CheckEventExist($event, $event_type, $event_source)
    {
        return false;
    }
}
