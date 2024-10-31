<?php
/** Read-More-Login plugin for WordPress.
 *  Puts a login/registration form in your posts and pages.
 *
 *  Copyright (C) 2018 Arild Hegvik
 *
 *  GNU GENERAL PUBLIC LICENSE (GNU GPLv3)
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace ARU_ReadMoreLogin;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WP_PluginFramework\PluginContainer' ) ) {
    include_once (__DIR__ . '/../vendor/centralplugin/wp-plugin-framework/src/WP_PluginFramework/PluginContainer.php');
}

use WP_PluginFramework;
use WP_PluginFramework\Utils\DebugLogger;
use WP_PluginFramework\Models\VersionInfo;
use WP_PluginFramework\Database\WpDbInterface;

class ReadMoreLoginPlugin extends WP_PluginFramework\PluginContainer
{
    const ARU_READMORELOGIN_SHORT_CODE = '[rml_read_more]';
    const ARU_READMORELOGIN_SHORT_CODE_END = '[/rml_read_more]';

    static public $PluginVersion;

    public function __construct()
    {
        /* Setting plugin file manually for quicker start-up */
        self::$PluginBaseFilePath =  ARU_READMORELOGIN_PLUGIN_FILE;

        /* Need to specify namespace as we have different namespace on the plugin framework */
        self::$PluginNamespace =  __NAMESPACE__;

        self::$AutoLoaderIncludes = array('models', 'views', 'controllers', 'src');

        parent::__construct();
    }

    public static function Instance()
    {
        if(is_null(self::$_instance))
        {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    protected function Includes()
    {
        parent::Includes();

        require_once 'CompactWidget.php';
    }

    protected function InitHooks()
    {
        parent::InitHooks();

        add_action('rml_hourly_event', array('ARU_ReadMoreLogin\Membership', 'ScheduleEvent'));
        add_action('init',  array('ARU_ReadMoreLogin\ContentLoader', 'PageLoadInit'));
        add_action('admin_menu', array('ARU_ReadMoreLogin\AdminPages', 'AdminMenu'));

        add_filter('login_url', array('ARU_ReadMoreLogin\Membership', 'RedirectLogin'));
        add_filter('logout_redirect',array('ARU_ReadMoreLogin\Membership', 'RedirectLogout'));
        add_filter('register_url', array('ARU_ReadMoreLogin\Membership', 'RedirectRegister'));
        add_filter('lostpassword_url', array('ARU_ReadMoreLogin\Membership', 'RedirectLostPassword'));

	    /* Pre-filtering text. Adjust line feed. */
	    add_filter('the_content', array('ARU_ReadMoreLogin\ContentLoader', 'FilterContentEarly'), 1);
	    /* Filter after block to remove <p> created at the block short-code. */
	    add_filter('the_content', array('ARU_ReadMoreLogin\ContentLoader', 'FilterContentAfterBlock'), 9);
        /* Main filter between do blocks and short-code. Want to process short-code in form. */
        add_filter('the_content', array('ARU_ReadMoreLogin\ContentLoader', 'FilterContent'), 10);

        add_filter('screen_settings', array('ARU_ReadMoreLogin\AdminPages', 'ScreenSettings'), 10, 2 );
        add_filter('set_screen_option_rml_show_wizard', array('ARU_ReadMoreLogin\AdminPages', 'SetScreenOptions'), 10, 2);

	    if (( defined( 'READ_MORE_LOGIN_ENTER_LICENSE_EDITION' ) and (READ_MORE_LOGIN_ENTER_LICENSE_EDITION === True))
	    or ( defined( 'READ_MORE_LOGIN_DOWNLOAD_PRE_RELEASE' ) and (READ_MORE_LOGIN_DOWNLOAD_PRE_RELEASE === True))
        or ( defined( 'READ_MORE_LOGIN_DOWNLOAD_DEV_VERSION' ) and (READ_MORE_LOGIN_DOWNLOAD_DEV_VERSION === True))) {
		    add_filter( 'site_transient_update_plugins', array('ARU_ReadMoreLogin\CustomUpgrade', 'site_transient_update_plugins'), 10, 2);
		    add_filter( 'plugins_api_result', array('ARU_ReadMoreLogin\CustomUpgrade', 'filter_plugins_api_result'), 10, 3);
		    add_action( 'after_plugin_row_read-more-login/read-more-login.php', array('ARU_ReadMoreLogin\CustomUpgrade', 'add_after_plugin_row'), 10, 3);
		    add_action( 'in_plugin_update_message-read-more-login/read-more-login.php', array('ARU_ReadMoreLogin\CustomUpgrade', 'add_in_plugin_update_message'), 10, 2);
	    }

        add_shortcode('rml_read_more', array('ARU_ReadMoreLogin\ContentLoader', 'InsertReadMoreLogin'));
        add_shortcode('rml_register', array('ARU_ReadMoreLogin\Membership', 'RegistrationPage'));
        add_shortcode('rml_login', array('ARU_ReadMoreLogin\Membership', 'MembershipLogin'));
        add_shortcode('rml_profile', array('ARU_ReadMoreLogin\Membership', 'ProfilePage'));
        add_shortcode('rml_reset_password', array('ARU_ReadMoreLogin\Membership', 'PasswordPage'));
        add_shortcode('rml_password', array('ARU_ReadMoreLogin\Membership', 'PasswordPage'));
        add_shortcode('rml_compact_login', array('ARU_ReadMoreLogin\CompactWidget', 'DrawCompactWidget')); //'ARU_ReadMoreLogin\rml_compact_login_widget');
    }

    public function GetDebugEnable()
    {
        if(!isset(self::$DebugEnable))
        {
            /* TODO: Sometime this option may not exist, use the class function */
            $options = get_option(SettingsAdvancedOptions::OPTION_NAME);
            self::$DebugEnable = $options[SettingsAdvancedOptions::ENABLE_DEBUG_LOG];
        }
        return self::$DebugEnable;
    }

    public function GetDebugLevel()
    {
        if(!isset(self::$DebugLevel))
        {
            self::$DebugLevel = DebugLogger::WARNING;
            $options = get_option(SettingsAdvancedOptions::OPTION_NAME);
            if($options[SettingsAdvancedOptions::ENABLE_EXTRA_DEBUG_LOGGING])
            {
                self::$DebugLevel = DebugLogger::NOTE;
            }
        }
        return self::$DebugLevel;
    }

    static public function ActivatePlugin()
    {
        parent::ActivatePlugin();

        if (!wp_next_scheduled('rml_hourly_event'))
        {
            wp_schedule_event(time(), 'hourly', 'rml_hourly_event');
        }
    }

    static public function DeactivatePlugin()
    {
        wp_clear_scheduled_hook('rml_hourly_event');
	    CustomUpgrade::delete_transient();

        parent::DeactivatePlugin();
    }

    static public function CheckUpgrade($previous_plugin_version=false)
    {
        if($previous_plugin_version === false)
        {
            static::InstallPlugin();

            /* First version did not set version option */
            $previous_plugin_version = PluginUpgrade::GetPreviousVersion();
            if($previous_plugin_version !== false)
            {
                static::Upgrade($previous_plugin_version);
            }
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
        parent::Upgrade($previous_plugin_version);

        $plugin_slug = self::GetPluginSlug();
        $plugin_version = parent::GetPluginVersion();

        PluginUpgrade::Upgrade($plugin_slug, $plugin_version, $previous_plugin_version);
    }

    static function UninstallPlugin()
    {
        $plugin_version = parent::GetPluginVersion();
        $plugin_slug = self::GetPluginSlug();

        $advanced_options = get_option(SettingsAdvancedOptions::OPTION_NAME);

        if ($advanced_options[SettingsAdvancedOptions::UNINSTALL_KEEP_DB_DATA])
        {
            DebugLogger::WriteDebugNote('Uninstalling '. $plugin_slug .' version ' . $plugin_version . '.');

            self::UpgradeVersionInfo($plugin_version, VersionInfo::PLUGIN_INSTALL_STATE_UNINSTALLED);
        }
        else
        {
            parent::UninstallPlugin();

            $debug_log_path = get_home_path() . 'wp-content/read_more_login_plugin.log';
            if(file_exists($debug_log_path))
            {
                unlink($debug_log_path);
            }
	
            /* Delete any version 1 options and data. Previous version did not uninstall all data. */
            delete_option('aru_readmorelogin_textfading_option');
            delete_option('aru_readmorelogin_text_fade_option');
            delete_option('aru_readmorelogin_searchengines_option');
            delete_option('aru_readmorelogin_debuglog_option');
            delete_option('aru_readmorelogin_linking_option');
            delete_option('aru_readmorelogin_redirect_option');
            delete_option('aru_readmorelogin_email_verification_option');
            delete_option('aru_readmorelogin_email_reset_password_option');
            delete_option('aru_readmorelogin_email_register_notification_option');

            $database = new WpDbInterface;
            if($database->TableExist('aru_readmorelogin_registration'))
            {
                $database->RemoveTable('aru_readmorelogin_registration');
            }
            if($database->TableExist('aru_readmorelogin_statistics'))
            {
                $database->RemoveTable('aru_readmorelogin_statistics');
            }
        }
    }
}
