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

use WP_PluginFramework\Utils\DebugLogger;

class Tbody extends HtmlBaseElement
{
    public function __construct($content=null, $attributes=null)
    {
        parent::__construct('tbody', true, $content, $attributes);
    }

    public function AddRow($content, $tr_attributes, $td_attributes)
    {
        $tr = null;

        $object_type = gettype($content);
        switch ($object_type)
        {
            case 'object':
                $class_type = get_class($content);
                switch($class_type)
                {
                    case 'Tr':
                        break;

                    case 'Th':
                    case 'Td':
                        $tr = new Tr($content, $tr_attributes);
                        break;

                    default:
                        $tr = new Tr($content, $tr_attributes);
                        break;
                }
                break;

            case 'array':
                /* TODO more code needed here to check other input scenarios */
                $tr = new Tr($content, $tr_attributes);
                break;

            case 'string':
                $td = new Td($content, $td_attributes);
                $tr = new Tr($td, $tr_attributes);
                break;

            default:
                DebugLogger::WriteDebugError('Unhandled data type ' . gettype($object_type));
                break;
        }

        if($tr)
        {
            $this->AddContent($tr);
        }
    }
}
