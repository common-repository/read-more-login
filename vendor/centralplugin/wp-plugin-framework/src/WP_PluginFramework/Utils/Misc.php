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

namespace WP_PluginFramework\Utils;

defined( 'ABSPATH' ) || exit;

class Misc
{
    static function rotateMatrix90($matrix)
    {
        $x_size = count($matrix);
        $y_size = count($matrix[0]);
        $matrix90 = array();
        for ($y = 0; $y < $y_size; $y++)
        {
            $row = array();
            for ($x = 0; $x < $x_size; $x++)
            {
                $arr = $matrix[$x];
                $text = $arr[$y];
                $row[] = $text;
            }
            $matrix90[] = $row;
        }
        return $matrix90;
    }

    static function RandomString($length)
    {
        $str = '';

        for ($i = 0; $i < $length; $i++)
        {
            $x = mt_rand(0, 61);
            if ($x < 10)
            {
                $y = strval($x);
            }
            elseif ($x < 36)
            {
                $y = chr(ord('a') + $x - 10);
            }
            else
            {
                $y = chr(ord('A') + $x - 36);
            }
            $str .= $y;
        }

        return $str;
    }
}