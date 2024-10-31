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

class TimeStampType extends DataType
{
    static $default_data_type = 'string';

    function __construct($metadata, $key, $value=null)
    {
        if(!isset($metadata['db_field_type'])) {
            $metadata['db_field_type'] = 'TIMESTAMP';
        }

        if(isset($metadata['default_value']))
        {
            switch (gettype($metadata['default_value']))
            {
                case 'integer':
                    if ($this->default_value >= 0)
                    {
                        $metadata['default_value'] = date('Y-m-d H:i:s', $this->default_value);
                    }
                    else
                    {
                        DebugLogger::WriteDebugError('Invalid default value ' . $value);
                    }
                    break;

                default:
                    DebugLogger::WriteDebugError('Unsupported data type for default value ' . gettype($value));
                    break;
            }
        }

        parent::__construct($metadata, $key, $value);
    }

    function SetValue($value)
    {
        switch (gettype($value))
        {
            case 'integer':
                $this->value = date('Y-m-d H:i:s', $value);
                break;

            default:
                DebugLogger::WriteDebugError('Unsupported data type ' . gettype($value));
                break;
        }
    }

    function GetValueSeconds()
    {
        return strtotime($this->value);
    }
}