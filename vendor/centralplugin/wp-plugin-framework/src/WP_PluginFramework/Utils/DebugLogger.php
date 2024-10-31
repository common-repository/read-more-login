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

class DebugLogger
{
    const ERROR = 0;
    const WARNING = 1;
    const NOTE = 2;

    const MAX_STRING_LENGTH_DUMP = 200;

    static $WriteWpLogToBuffer = false;
    static $WriteWpLogBuffer = array();

    static function WriteDebugError($data1 = null, $data2 = null, $data3 = null)
    {
        self::WriteDebugLog(self::ERROR, $data1, $data2, $data3);
    }

    static function WriteDebugWarning($data1 = null, $data2 = null, $data3 = null)
    {
        $plugin = PluginContainer::Instance();

        if(($plugin->GetDebugEnable()) and ($plugin->GetDebugLevel() >= self::WARNING))
        {
            self::WriteDebugLog(self::WARNING, $data1, $data2, $data3);
        }
    }

    static function WriteDebugNote($data1 = null, $data2 = null, $data3 = null)
    {
        $plugin = PluginContainer::Instance();

        if(($plugin->GetDebugEnable()) and ($plugin->GetDebugLevel() >= self::NOTE))
        {
            self::WriteDebugLog(self::NOTE, $data1, $data2, $data3);
        }
    }

    static function PauseWpDebugLogging()
    {
        self::$WriteWpLogToBuffer = true;
    }

    static function ContinueWpDebugLogging()
    {
        self::$WriteWpLogToBuffer = false;

        foreach (self::$WriteWpLogBuffer as $log_msg)
        {
            error_log($log_msg, 0);
        }

        self::$WriteWpLogBuffer = array();
    }

    static function Obfuscate($text)
    {
        $pieces = explode("@", $text);
        $text = '';
        foreach ($pieces as $piece)
        {
            if($text){$text .= '@';}
            $n = strlen($piece);
            if($n>3)
            {
                $fill = str_repeat("*", $n - 2);
                $text .= substr_replace($piece, $fill, 1, strlen($piece) - 2);
            }
            else if($n>1)
            {
                $fill = str_repeat("*", $n - 2);
                $text .= substr_replace($piece, $fill, 1, strlen($piece) - 2);
            }
            else
            {
                $text .= str_repeat("*", $n);;
            }
        }

        return $text;
    }

    static function WriteDebugLog($type, $data1, $data2, $data3)
    {
        $trace = debug_backtrace();
        $txt = self::GetTrace($trace,2);

        if ($data1)
        {
            $txt .= ' ' . self::safe_dump($data1);
        }

        if ($data2)
        {
            $txt .= ' ' . self::safe_dump($data2);
        }

        if ($data3)
        {
            $txt .= ' ' . self::safe_dump($data3);
        }

        self::WriteDebugFile($type, $txt);

        if($type == self::ERROR)
        {
            for($level=1; $level<=4; $level++)
            {
                $txt = self::GetTrace($trace, $level+2);
                if($txt)
                {
                    $txt = 'Trace ' . $level . ': ' . $txt;
                    self::WriteDebugFile($type, $txt);
                }
            }
        }
    }

    static function GetTrace($trace, $level)
    {
        $txt = '';

        if($level < count($trace))
        {
            if (isset($trace[$level]['class']))
            {
                $txt = $trace[$level]['class'];

                if (isset($trace[$level]['type']))
                {
                    $txt .= $trace[$level]['type'];
                }

                if (isset($trace[$level]['function']))
                {
                    $txt .= $trace[$level]['function'];
                }

                if (isset($trace[$level - 1]['line']))
                {
                    $txt .= '(' . $trace[$level - 1]['line'] . ')';
                }
            }
            else if (isset($trace[$level]['function']))
            {
                $txt = $trace[$level]['function'];

                if (isset($trace[$level - 1]['line']))
                {
                    $txt .= '(' . $trace[$level - 1]['line'] . ')';
                }
            }
            else if (isset($trace[$level]['file']))
            {
                if (isset($trace[$level - 1]['line']))
                {
                    $txt .= '(' . $trace[$level - 1]['line'] . ')';
                }
            }
            else
            {
                $txt = '?';
            }
        }
        return $txt;
    }

    static function WriteDebugFile($type, $txt)
    {
        $plugin = PluginContainer::Instance();

        switch($type)
        {
            case self::ERROR:
                $type_txt = 'ERROR';
                break;
            case self::WARNING:
                $type_txt = 'WARNING';
                break;
            case self::NOTE:
                $type_txt = 'NOTE';
                break;
            default:
                $type_txt = 'UNDEFINED';
                break;
        }

        if(($plugin->GetDebugLevel() >= self::NOTE) and (function_exists('wp_get_current_user')))
        {
            $current_user = wp_get_current_user();
            $user_id = $current_user->ID;
            $user_txt = 'User=' . $user_id . ':';
        }
        else
        {
            $user_txt = '';
        }

        if($plugin->GetDebugEnable())
        {
            $log_msg = '[' . date("d-M-Y H:i:s ", time()) . 'UTC] ' . $type_txt . ' '. $user_txt . $txt . "\r\n";
            error_log($log_msg, 3, WP_CONTENT_DIR . '/read_more_login_plugin.log');
        }

        if($type == self::ERROR)
        {
            $log_msg = $type_txt . ' ' . $user_txt . ':' . $txt;

            if(self::$WriteWpLogToBuffer)
            {
                self::$WriteWpLogBuffer[] = $log_msg;
            }
            else
            {
                /* Always write error message to WordPress debug log. WP_DEBUG and WP_DEBUG_LOG must be defined. */
                error_log($log_msg, 0);
            }
        }
    }

    static function safe_dump($data, $clamps = '')
    {
        $txt = '';

        switch (gettype($data)) {
            case 'array':
                $txt .= '{';
                foreach ($data as $key => $value) {
                    if ($key == 'password') {
                        $value = '***';
                    }

                    $txt .= self::safe_dump($key) . '=' . self::safe_dump($value, '"') . ';';
                }
                $txt .= '}';
                break;

            case 'integer':
                $txt = strval($data);
                break;

            case 'string':
                if (strlen($data) > self::MAX_STRING_LENGTH_DUMP) {
                    if ($clamps) {
                        $txt .= $clamps . substr($data, 0, self::MAX_STRING_LENGTH_DUMP) . $clamps . '...(strlen=' . strlen($data) . ')';
                    } else {
                        $txt .= substr($data, 0, self::MAX_STRING_LENGTH_DUMP) . '...(strlen=' . strlen($data) . ')';
                    }
                } else {
                    if ($clamps) {
                        $txt .= $clamps . $data . $clamps;
                    } else {
                        $txt .= $data;
                    }
                }
                break;

            case 'boolean':
                if ($data) {
                    $txt .= 'true';
                } else {
                    $txt .= 'false';
                }
                break;

            case 'NULL':
                $txt .= 'NULL';
                break;

            default:
                self::WriteDebugError(' invalid data type:', gettype($data));
                die('invalid data type ' . gettype($data));
                break;
        }

        return $txt;
    }
}
