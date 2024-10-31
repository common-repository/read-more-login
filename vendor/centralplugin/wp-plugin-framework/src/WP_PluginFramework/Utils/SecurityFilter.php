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

use WP_PluginFramework\PluginContainer;

class SecurityFilter
{
    const POSITIVE_INTEGER = 'positive_integer';
    const POSITIVE_INTEGER_ZERO = 'positive_integer_zero';
    const BOOL = 'bool';
    const STRING_KEY_NAME = 'string_key_name';
    const ALPHA_NUM = 'alpha_num';
    const ALPHA = 'alpha';
    const WP_USERNAME = 'wp_username';
    const WP_PASSWORD = 'wp_password';
    const EMAIL = 'email';
    const CLASS_NAME = 'class_name';

    static function SanitizeText($text)
    {
        if (preg_match('/^[A-Za-z0-9 .,;:+=?_~\/\-!@#\$%\^&\*\(\)]+$/', $text)) {
            return strval($text);
        } else {
            return null;
        }
    }

    static function SanitizeAlphaNumText($text)
    {
        if (preg_match('/^[A-Za-z0-9]+$/', $text)) {
            return strval($text);
        } else {
            return null;
        }
    }

    static function SanitizeAlphaText($text)
    {
        if (preg_match('/^[A-Za-z]+$/', $text)) {
            return strval($text);
        } else {
            return null;
        }
    }

    static function SanitizeKeyNameText($text)
    {
        if (preg_match('/^[A-Za-z0-9_\-]+$/', $text)) {
            return strval($text);
        } else {
            return null;
        }
    }

    static function SanitizeInteger($text)
    {
        if (preg_match('/^[1-9][0-9]{0,15}$/', $text)) {
            $value = intval($text);
            if (gettype($value) == 'integer') {
                return $value;
            }
        }

        return null;
    }

    static function SanitizePositiveIntegerOrZero($text)
    {
        if (preg_match('/^[1-9][0-9]{0,15}$/', $text)) {
            $value = intval($text);
            if (gettype($value) == 'integer') {
                if ($value >= 0) {
                    return $value;
                }
            }
        }

        return null;
    }

    static function SanitizePositiveInteger($text)
    {
        if (preg_match('/^[1-9][0-9]{0,15}$/', $text)) {
            $value = intval($text);
            if (gettype($value) == 'integer') {
                if ($value > 0) {
                    return $value;
                }
            }
        }

        return null;
    }

    static function SanitizeFloat($text)
    {
        if (preg_match('/^[0-9,.\-]{0,15}$/', $text)) {
            $value = floatval($text);
            if ((gettype($value) == 'float') or (gettype($value) == 'double')) {
                return $value;
            }
        }

        return null;
    }

    static function SanitizeBool($text)
    {
        if (preg_match('/^[01]$/', $text)) {
            $value = ($text == '1');
        } else {
            $value = null;
        }

        return $value;
    }

    static function SanitizeClassName($text)
    {
        if (preg_match('/^[A-Za-z0-9_\\\]+$/', $text)) {
            if(strlen ($text) < 256) {
                return strval($text);
            }
        } else {
            return null;
        }
    }

    static function filter($unfiltered, $filter_datatype)
    {
        switch ($filter_datatype) {
            case 'string':
            case SecurityFilter::WP_USERNAME:
            case SecurityFilter::WP_PASSWORD:
            case SecurityFilter::EMAIL:
                return self::SanitizeText($unfiltered);
                break;

            case SecurityFilter::STRING_KEY_NAME:
                return self::SanitizeKeyNameText($unfiltered);
                break;

            case SecurityFilter::ALPHA_NUM:
                return self::SanitizeAlphaNumText($unfiltered);
                break;

            case SecurityFilter::ALPHA:
                return self::SanitizeAlphaText($unfiltered);
                break;

            case 'integer':
                return self::SanitizeInteger($unfiltered);
                break;

            case SecurityFilter::POSITIVE_INTEGER:
                return self::SanitizePositiveInteger($unfiltered);
                break;

            case SecurityFilter::POSITIVE_INTEGER_ZERO:
                return self::SanitizePositiveIntegerOrZero($unfiltered);
                break;

            case 'double':
                return self::SanitizeFloat($unfiltered);
                break;

            case SecurityFilter::BOOL:
                return self::SanitizeBool($unfiltered);
                break;

            case SecurityFilter::CLASS_NAME:
                return self::SanitizeClassName($unfiltered);
                break;

            default:
                throw new \Exception('Undefined filter');
        }
    }

    static function FilterList($unfiltered_list, $filter)
    {
        $filtered_list = array();
        foreach ($unfiltered_list as $unfiltered) {
            $filtered = self::filter($unfiltered, $filter);
            if ($filtered != null) {
                $filtered_list[] = $filtered;
            }
        }
        return $filtered_list;
    }

    static function SafeReadGetRequest($key, $filter_datatype)
    {
        if (isset($_GET[$key]) && $_GET[$key] != '') {
            return self::filter($_GET[$key], $filter_datatype);
        } else {
            return null;
        }
    }

    static function SafeReadGetKeyList()
    {
        $unfiltered_list = array();
        foreach ($_GET as $key => $value) {
            $unfiltered_list[] = $key;
        }
        $filtered_list = self::FilterList($unfiltered_list, SecurityFilter::STRING_KEY_NAME);
        return $filtered_list;
    }

    static function SafeReadPostRequest($key, $filter)
    {
        if (isset($_POST[$key])) {
            return self::filter($_POST[$key], $filter);
        } else {
            return null;
        }
    }

    static function SafeReadPostKeyList()
    {
        $unfiltered_list = array();
        foreach ($_POST as $key => $value) {
            $unfiltered_list[] = $key;
        }
        $filtered_list = self::FilterList($unfiltered_list, SecurityFilter::STRING_KEY_NAME);
        return $filtered_list;
    }

    static function SafeReadCookieString($cookie_name, $filter)
    {
        if (isset($_COOKIE[$cookie_name])) {
            return self::filter($_COOKIE[$cookie_name], $filter);
        } else {
            $cookie = null;

        }

        return $cookie;
    }

    static function SafeReadArrayItem($array, $key, $filter)
    {
        if (isset($array[$key])) {
            return self::filter($array[$key], $filter);
        } else {
            return null;
        }
    }

    static function SanitizeTextAlpanumeric($text)
    {
        return true;
    }

    static function SanitizeTextAlpanumericSymbols($text)
    {
        return true;

    }

    static function RemoveListItem($list, $item)
    {
        $position = array_search($item, $list);
        if ($position !== null) {
            unset($list[$position]);
        }
        return $list;
    }
}
