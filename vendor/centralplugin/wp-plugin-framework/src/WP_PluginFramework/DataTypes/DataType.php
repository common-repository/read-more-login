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

namespace WP_PluginFramework\DataTypes;

defined( 'ABSPATH' ) || exit;

use WP_PluginFramework\Base\BaseObject;
use WP_PluginFramework\Utils\DebugLogger;

class DataType extends BaseObject
{
    static $default_data_type=null;
    static $default_string_validation=null;

    protected $data_size = null;
    protected $default_value = null;
    protected $db_field_type = null;
    protected $db_collation = null;
    protected $value = null;
    protected $ValidateErrors = array();
    protected $html_widget_class = null;

    const DATA_SIZE ='data_size';
    const DEFAULT_VALUE ='default_value';
    const DB_FIELD_TYPE ='db_field_type';
    const HTML_WIDGET_CLASS ='html_widget_class';

    function __construct($properties, $key, $value=null)
    {
        if(isset($properties)){
            if(gettype($properties) != 'array'){
                DebugLogger::WriteDebugError('Metadata wrong type ' . gettype($properties));
            }
        }

        if(!isset($properties['data_type']))
        {
            $properties['data_type'] = static::$default_data_type;
        }

        if(!isset($properties['db_field_type']))
        {
            switch (static::$default_data_type)
            {
                case 'integer':
                    if(!isset($properties['data_size'])) {
                        $properties['data_size'] = 8;
                    }
                    $properties['db_field_type'] = 'INT('.strval($properties['data_size']).')';
                    break;

                case 'string':
                    if(!isset($properties['data_size'])) {
                        $properties['data_size'] = 16;
                    }
                    $properties['db_field_type'] = 'VARCHAR('.strval($properties['data_size']).')';
                    break;

                case 'double':
                    $properties['db_field_type'] = 'DOUBLE';
                    break;

                default:
                    DebugLogger::WriteDebugError('Missing property \'db_field_type\' data type"');
                    break;
            }
        }

        if(!isset($properties['string_validation']))
        {
            if(isset(static::$default_string_validation))
            {
                $properties['string_validation'] = static::$default_string_validation;
            }
        }

        $properties['name'] = $key;

        if(isset($value)){
            $this->value = $value;
        } else {
            $this->value = $this->default_value;
        }

        parent::__construct($properties);
    }

    static function GetType()
    {
        return static::$default_data_type;
    }

    public function GetDefaultValue()
    {
        return $this->default_value;
    }

    public function GetDatabaseType()
    {
        return $this->db_field_type;
    }

    public function GetDatabaseCollation()
    {
        return $this->db_collation;
    }

    public function Validate($value)
    {
        if(isset($this->string_validation))
        {
            $value_str = strval($value);
            return preg_match($this->string_validation, $value_str);
        }
        else
        {
            return true;
        }
    }

    public function AddValidateErrors($errors)
    {
        return $this->AddProperty('ValidateErrors', $errors);
    }

    public function GetValidateErrors()
    {
        return $this->GetProperty('ValidateErrors');
    }

    public function GetValue()
    {
        return $this->value;
    }

    public function GetString()
    {
        return strval($this->value);
    }

    function GetFormattedText()
    {
        return $this->GetString();
    }

    function GetHtmlComponent($attributes=null)
    {
        $html_component = null;
        $html_component_class = $this->html_widget_class;

        if(!strpos($html_component_class, '\\'))
        {
            /* If only class name set, use vendor's default HtmlComponents */
            $namespace = explode('\\',__NAMESPACE__);
            $html_component_class = $namespace[0] . '\\HtmlComponents\\' . $html_component_class;
        }

        if(isset($html_component_class))
        {
            $html_component = new $html_component_class($attributes, $this->GetProperties());
        }
        return $html_component;
    }
}
