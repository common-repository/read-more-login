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

class IntegerType extends DataType
{
    static $default_data_type = 'integer' ;
    static $default_string_validation = '/^[0-9]+/';

    function __construct($metadata, $key, $value=null)
    {
        parent::__construct($metadata, $key, $value);
    }

    function GetFormattedText()
    {
        $s = number_format($this->value, 0, '', ',');
        return $s;
    }

    function SetValue($value)
    {
        switch (gettype($value))
        {
            case 'string':
                $this->value = intval($value);
                break;

            case 'integer':
                $this->value = $value;
                break;

            default:
                DebugLogger::WriteDebugError('Unsupported data type ' . gettype($value));
                break;
        }
    }
}
