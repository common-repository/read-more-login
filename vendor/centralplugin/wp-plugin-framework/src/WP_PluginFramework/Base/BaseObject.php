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

namespace WP_PluginFramework\Base;

defined( 'ABSPATH' ) || exit;

class BaseObject
{
    /** @var array BaseObject */
    protected $contents = array();

    public function __construct($properties=null)
    {
        if(isset($properties))
        {
            $this->SetProperties($properties);
        }
    }

    public function SetProperties($properties)
    {
        foreach ($properties as $key => $value)
        {
            $this->$key = $value;
        }
    }

    public function GetProperties()
    {
        $properties = get_object_vars($this);
        return $properties;
    }

    public function SetProperty($property, $value)
    {
        $this->$property = $value;
    }

    public function AddProperty($property, $values)
    {
        if (isset($this->$property))
        {
            if (is_array($this->$property))
            {
                if (empty($this->$property))
                {
                    if (is_array($values))
                    {
                        $this->$property = $values;
                    }
                    else
                    {
                        array_push($this->$property, $values);
                    }
                }
                else
                {
                    if (is_array($values))
                    {
                        $this->$property = array_merge($this->$property, $values);
                    }
                    else
                    {
                        array_push($this->$property, $values);
                    }
                }
                return true;
            }
            else
            {
                return false;
            }
        }
        else
        {
            return null;
        }
    }

    public function GetProperty($property)
    {
        if (isset($this->$property))
        {
            return $this->$property;
        }
        else
        {
            return null;
        }
    }

    /**
     * Add values to an property array given with key.
     *
     * $this->$property[$key] = {array of values}
     *
     * @param $property
     * @param $key          string
     * @param $values       array|string
     * @return bool|null    Return null if property does not exist. True on success. False on failure.
     */
    protected function SetPropertyKeyValues($property, $key, $values)
    {
        if (isset($this->$property))
        {
            if(is_array($this->$property))
            {
                if (array_key_exists($key, $this->$property))
                {
                    if (is_array($this->$property))
                    {
                        if (is_array($values))
                        {
                            /* TODO: Rewrite this. Could not change property array due to PHP 5.6 compatibility. */
                            $tmp = $this->$property;
                            $tmp[$key] = array_merge($this->$property[$key], $values);
                            $this->$property = $tmp;

                        }
                        else
                        {
                            /* TODO: Rewrite this. Could not change property array due to PHP 5.6 compatibility. */
                            $tmp = $this->$property;
                            $value_arr = array($values);
                            $tmp[$key] = array_merge($tmp[$key], $value_arr);
                            $this->$property = $tmp;
                        }
                    }
                    else
                    {
                        return false;
                    }
                }
                else
                {
                    if (is_array($values))
                    {
                        $this->$property[$key] = $values;
                    }
                    else
                    {
                        /* TODO: Rewrite this. Could not change property array due to PHP 5.6 compatibility. */
                        $tmp = $this->$property;
                        $tmp[$key] = array($values);
                        $this->$property = $tmp;
                    }
                }
            }
            return true;
        }
        else
        {
            return null;
        }
    }

    protected function RemovePropertyKeyValues($property, $key, $values)
    {
        if (isset($this->$property))
        {
            if (is_array($this->$property))
            {
                if(array_key_exists($key, $this->$property))
                {
                    if (is_array($values))
                    {
                        /* TODO: Rewrite this. Could not change property array due to PHP 5.6 compatibility. */
                        $tmp = $this->$property;
                        $tmp[$key] = array_diff($this->$property[$key], $values);
                        $this->$property = $tmp;
                    }
                    else
                    {
                        /* TODO: Rewrite this. Could not change property array due to PHP 5.6 compatibility. */
                        $tmp = $this->$property;
                        $value_arr = array($values);
                        $tmp[$key] = array_diff($tmp[$key], $value_arr);
                        $this->$property = $tmp;
                    }
                    return true;
                }
                else
                {
                    return false;
                }
            }
            else
            {
                return null;
            }
        }
        else
        {
            return null;
        }
    }

    protected function GetPropertyKeyValues($property, $key)
    {
        if (isset($this->$property))
        {
            if(isset($this->$property[$key]))
            {
                return $this->$property[$key];
            }
        }
        return null;
    }

    public function SetContent($content)
    {
        $this->contents = array();

        if(isset($content))
        {
            switch (gettype($content))
            {
                case 'array';
                    $this->contents = $content;
                    break;

                default:
                    $this->contents = array($content);
            }
        }
    }

    public function AddContent($content)
    {
        switch(gettype($content))
        {
            case 'array';
                $this->contents = array_merge($this->contents, $content);
                break;

            default:
                array_push($this->contents, $content);
        }
    }

    public function PrependContent($content)
    {
        switch(gettype($content))
        {
            case 'array';
                $this->contents = array_unshift ($this->contents, $content);
                break;

            default:
                array_unshift ($this->contents, $content);
        }
    }

    public function GetContent()
    {
        return $this->contents;
    }
}
