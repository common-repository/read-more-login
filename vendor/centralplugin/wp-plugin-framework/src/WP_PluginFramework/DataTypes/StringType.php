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

class StringType extends DataType
{
    static $default_data_type = 'string';

    function __construct($properties, $key, $value=null)
    {
        if(!isset($properties['data_size'])) {
            $properties['data_size'] = 256;
        }

        if(!isset($properties['db_field_type'])) {
            $properties['db_field_type'] = 'VARCHAR('. $properties['data_size'] . ')';
        }

        if(!isset($properties['db_collation'])) {
            if(defined('DB_COLLATE') && DB_COLLATE !== '')
            {
                $properties['db_collation'] = DB_COLLATE;
            }
            else
            {
                $properties['db_collation'] = 'utf8mb4_unicode_ci';
            }
        }

        if(!isset($properties[self::HTML_WIDGET_CLASS])) {
            $properties[self::HTML_WIDGET_CLASS] = 'TextLine';
        }

        parent::__construct($properties, $key, $value);
    }
}

