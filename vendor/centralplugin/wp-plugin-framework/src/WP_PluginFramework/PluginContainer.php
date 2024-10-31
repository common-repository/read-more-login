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

namespace WP_PluginFramework;

defined( 'ABSPATH' ) || exit;

use WP_PluginFramework\Controllers\Controller;
use WP_PluginFramework\Utils\SecurityFilter;
use WP_PluginFramework\Utils\DebugLogger;
use WP_PluginFramework\Models\VersionInfo;

class PluginContainer
{
    const WP_PLUGIN_MVC_FRAMEWORK_VERSION = '0.0.3.3';
    const WP_PLUGIN_FRAMEWORK_AJAX_HANDLER = 'wp_plugin_framework_ajax_handler';
    const WP_PLUGIN_FRAMEWORK_INIT_KEY =  '_init_callback';

    protected static $_instance = null;
    static private $PluginData = null;

    protected static $PluginBaseFilePath = null;
    protected static $PluginNamespace = null;
    private  static $WpFrameworkNamespace = null;
    protected static $AutoLoaderIncludes = array();

    public static $PermittedHtmlElements = array('b','i','u','strong','h1','h2','h3','h4','h5','h6','hr','br');

    public static $DebugEnable = null;
    public static $DebugLevel = null;

    public function __construct()
    {
        static::GetPluginData();

        if(!isset(self::$PluginBaseFilePath))
        {
            self::$PluginBaseFilePath = self::$PluginData['PluginFile'];
        }

        if(!isset(self::$PluginNamespace))
        {
            self::$PluginNamespace =  __NAMESPACE__;
        }

        self::$WpFrameworkNamespace = __NAMESPACE__;

        $this->DefineConstants();
        $this->Includes();
        $this->InitHooks();
    }

    public static function Instance()
    {
        if(is_null(self::$_instance))
        {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    protected function DefineConstants()
    {
    }

    protected function Includes()
    {
        require_once (dirname(self::$PluginBaseFilePath) . '/vendor/autoload.php');
    }

    protected function InitHooks()
    {
        spl_autoload_register(array($this, 'AutoLoader'));

        add_action('plugins_loaded', array($this, 'PluginLoaded'));
        add_action('init', array($this, 'Init'));
        add_action('wp_ajax_' . self::WP_PLUGIN_FRAMEWORK_AJAX_HANDLER, array($this, 'AjaxHandler'));
        add_action('wp_ajax_nopriv_' . self::WP_PLUGIN_FRAMEWORK_AJAX_HANDLER, array($this, 'AjaxHandler'));

        register_activation_hook(self::$PluginBaseFilePath , array( get_called_class(), 'ActivatePlugin' ) );
        register_deactivation_hook(self::$PluginBaseFilePath , array( get_called_class(), 'DeactivatePlugin' ) );
        register_uninstall_hook(self::$PluginBaseFilePath, array( get_called_class(), 'UninstallPlugin' ));
    }

    static public function AutoLoader($className)
    {
        if (strncmp($className, self::$PluginNamespace, strlen(self::$PluginNamespace)) === 0)
        {
            $className2 = substr($className, strlen(self::$PluginNamespace) + 1);

            foreach (self::$AutoLoaderIncludes as $include)
            {
                $filename = plugin_dir_path(self::$PluginBaseFilePath) . "/" . $include . "/" . str_replace("\\", '/', $className2) . ".php";
                if (file_exists($filename))
                {
                    include($filename);
                    if (class_exists($className))
                    {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    public function PluginLoaded()
    {
        $language_path = $this->GetPluginSlug() . '/languages/';
        load_plugin_textdomain(self::$PluginData['TextDomain'], FALSE, $language_path);

        $plugin_version_installed = $this->GetExistingDbVersion();
        if($plugin_version_installed === false)
        {
            $plugin_version = static::GetPluginVersion();
            static::UpgradeVersionInfo($plugin_version, VersionInfo::PLUGIN_INSTALL_STATE_ACTIVATED);
        }
        $this->CheckUpgrade($plugin_version_installed);
    }

    protected function SafeCreateController()
    {
        $controller_name = SecurityFilter::SafeReadPostRequest(Controller::PROTECTED_DATA_CONTROLLER, SecurityFilter::CLASS_NAME);

        if($controller_name)
        {
            /* jQuery ajax post adds double slashes */
            $controller_class = stripslashes($controller_name);

            $controller_class_data = explode('\\', $controller_class);
            if (count($controller_class_data) === 2)
            {
                if ($controller_class_data[0] === self::$PluginNamespace)
                {
                    if (substr($controller_class_data[1], -10) === 'Controller')
                    {
                        $safe_controller_class = $controller_class_data[0] . '\\' . $controller_class_data[1];
                        if(class_exists($safe_controller_class))
                        {
                            $controller = new $safe_controller_class();
                            return $controller;
                        }
                    }
                }
            }
        }

        /* Don't flood the debug log due to hacking attempt. Write to buffer until nonce confirmed. */
        DebugLogger::PauseWpDebugLogging();

        DebugLogger::WriteDebugError('Invalid controller name ' . $controller_name);

        return false;
    }

    function Init()
    {
        $init_event  = SecurityFilter::SafeReadPostRequest(self::WP_PLUGIN_FRAMEWORK_INIT_KEY, SecurityFilter::CLASS_NAME);

        if(isset($init_event))
        {
            $controller = self::SafeCreateController();
            if($controller !== false)
            {
                $controller->InitHandler($init_event);
            }
        }
    }

    function AjaxHandler()
    {
        $controller = self::SafeCreateController();

        if($controller !== false)
        {
            $response = $controller->AjaxHandler();
            echo $response;
        }

        die();
    }

    static function GetPluginData()
    {
        if (!isset(self::$PluginData))
        {
            /* Extract plugin base file from current file path. */
            $filename_length = strlen(__FILE__);
            $basename = plugin_basename(__FILE__);
            $basename_length = strlen($basename);
            $diff = $filename_length - $basename_length;
            $path = substr(__FILE__, 0, $diff);
            $plugin_slug = substr($basename, 0, strpos($basename, '/'));
            $plugin_file = $path . $plugin_slug . '/' . $plugin_slug . '.php';

            require_once ABSPATH . 'wp-admin/includes/plugin.php';

            self::$PluginData = get_plugin_data($plugin_file, false, false);
            self::$PluginData['PluginFile'] = $plugin_file;
	        self::$PluginData['PluginSlug'] = $plugin_slug;
	        self::$PluginData['Version'] = strtolower(trim(self::$PluginData['Version']));
	        $split = explode(' ', self::$PluginData['Version']);
	        if(count ($split) > 1) {
		        self::$PluginData['Edition'] = $split[0];
		        self::$PluginData['Version'] = $split[1];
	        } else {
		        self::$PluginData['Edition'] = 'standard';
	        }

            /* Create a plugin prefix from author name, using 3 first letter converted to lower case. */
            self::$PluginData['PluginPrefix'] = substr(strtolower(preg_replace("/[^a-zA-Z]+/", "", self::$PluginData['Author'])), 0, 3);
        }
    }

    static function GetWpFrameworkNamespace()
    {
        return self::$WpFrameworkNamespace;
    }

	static function GetPluginEdition()
	{
		return self::$PluginData['Edition'];
	}

    static function GetPluginVersion()
    {
        return self::$PluginData['Version'];
    }

    static function GetPluginSlug()
    {
        return self::$PluginData['PluginSlug'];
    }

    static function GetPrefixedPluginSlug()
    {
        return self::$PluginData['PluginPrefix'] . '_' . self::$PluginData['PluginSlug'];
    }

    static function GetPluginName()
    {
        return self::$PluginData['Name'];
    }

    public function GetDebugEnable()
    {
        if(!isset(self::$DebugEnable))
        {
            self::$DebugEnable = 1;
        }
        return self::$DebugEnable;
    }

    public function GetDebugLevel()
    {
        if(!isset(self::$DebugLevel))
        {
            self::$DebugLevel = DebugLogger::WARNING;
        }
        return self::$DebugLevel;
    }

    static public function ActivatePlugin()
    {
        $plugin_version = static::GetPluginVersion();
        $plugin_slug = static::GetPluginSlug();

        DebugLogger::WriteDebugNote('Activating ' . $plugin_slug . ' version ' . $plugin_version . '.');

        $language_path = static::GetPluginSlug() . '/languages/';
        load_plugin_textdomain(self::$PluginData['TextDomain'], FALSE, $language_path);

        $previous_plugin_version = static::UpgradeVersionInfo($plugin_version, VersionInfo::PLUGIN_INSTALL_STATE_ACTIVATED);

        static::CheckUpgrade($previous_plugin_version);
    }

    static public function DeactivatePlugin()
    {
        $plugin_version = static::GetPluginVersion();
        $plugin_slug = static::GetPluginSlug();

        DebugLogger::WriteDebugNote('Deactivating ' . $plugin_slug . ' version ' . $plugin_version . '.');

        static::UpgradeVersionInfo($plugin_version, VersionInfo::PLUGIN_INSTALL_STATE_DEACTIVATED);
    }

    static function UninstallPlugin()
    {
        $path = plugin_dir_path(self::$PluginBaseFilePath) . 'models';

        if (is_dir($path))
        {
            $filenames = scandir($path);

            foreach($filenames as $filename)
            {
                if($filename[0] !== '.')
                {
                    $class_name = self::$PluginNamespace . '\\' . substr($filename, 0, strpos($filename,'.'));
                    if (class_exists($class_name, true))
                    {
                        $model = new $class_name();
                        $model->Remove();
                    }
                }
            }
        }

        $option_name = self::GetPrefixedPluginSlug() . '_version';
        $version_data = new VersionInfo($option_name);
        $version_data->Remove();
    }

    static public function InstallPlugin()
    {
        $plugin_version = self::GetPluginVersion();
        $plugin_slug = self::GetPluginSlug();

        DebugLogger::WriteDebugNote('Install ' . $plugin_slug . ' version ' . $plugin_version . '.');

        $path = plugin_dir_path(self::$PluginBaseFilePath) . 'models';

        if (is_dir($path))
        {
            $filenames = scandir($path);

            foreach($filenames as $filename)
            {
                if($filename[0] !== '.')
                {
                    $class_name = self::$PluginNamespace . '\\' . substr($filename, 0, strpos($filename,'.'));
                    if (class_exists($class_name, true))
                    {
                        $model = new $class_name();
                        $model->Create();
                    }
                }
            }
        }
    }

    static public function CheckUpgrade($previous_plugin_version=false)
    {
        if($previous_plugin_version === false)
        {
            static::InstallPlugin();
        }
        else
        {
            $my_plugin_version = static::GetPluginVersion();
            if($my_plugin_version != $previous_plugin_version)
            {
                static::Upgrade($previous_plugin_version);
            }
        }
    }

    static public function Upgrade($previous_plugin_version=false)
    {
        $plugin_version = self::GetPluginVersion();
        $plugin_slug = self::GetPluginSlug();
        DebugLogger::WriteDebugNote( 'Upgrade ' . $plugin_slug . ' from version ' . $previous_plugin_version . ' to ' . $plugin_version . '.' );
        self::UpgradeVersionInfo(self::GetPluginVersion());
    }

    static public function UpgradeVersionInfo($plugin_version, $install_state=false)
    {
        $plugin_slug = static::GetPluginSlug();
        $plugin_name = static::GetPluginName();
        $option_name = static::GetPrefixedPluginSlug() . '_version';
        $version_data = new VersionInfo($option_name);

        return  $version_data->SaveVersionInfo($plugin_version, self::WP_PLUGIN_MVC_FRAMEWORK_VERSION, $plugin_slug, $plugin_name, $install_state);
    }

    static public function GetExistingDbVersion()
    {
        $option_name = static::GetPrefixedPluginSlug() . '_version';
        $option = get_option($option_name);
        if($option !== false)
        {
            return $option[VersionInfo::PLUGIN_VERSION];
        }
        return false;
    }
}
