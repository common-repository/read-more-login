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

use WP_PluginFramework\Utils\DebugLogger;

class BooleanType extends DataType
{
    static $default_data_type = 'integer' ;

    function __construct($metadata, $key, $value=null)
    {
        if(!isset($metadata['db_field_type'])) {
            $metadata['db_field_type'] = 'BOOLEAN';
        }

        if(!isset($metadata[self::HTML_WIDGET_CLASS])) {
            $metadata[self::HTML_WIDGET_CLASS] = 'CheckBox';
        }

        parent::__construct($metadata, $key, $value);
    }

    function GetFormattedText()
    {
        if(isset($this->value))
        {
            if($this->value)
            {
                $s = 'true';
            }
            else
            {
                $s = 'false';
            }
        }
        else
        {
            $s = 'null';
        }

        return $s;
    }

    function SetValue($value)
    {
        switch (gettype($value))
        {
            case 'string':
                if ($value === '1')
                {
                    $this->value = 1;
                }
                else if ($value === '0')
                {
                    $this->value = 0;
                }
                else
                {
                    DebugLogger::WriteDebugError('Invalid string value ' . gettype($value));
                }
                break;

            case 'boolean':
                if ($value === true)
                {
                    $this->value = 1;
                }
                else if ($value === false)
                {
                    $this->value = 0;
                }
                else
                {
                    DebugLogger::WriteDebugError('Invalid boolean value ' . gettype($value));
                }
                break;

            default:
                DebugLogger::WriteDebugError('Unsupported data type ' . gettype($value));
                break;
        }
    }
}
