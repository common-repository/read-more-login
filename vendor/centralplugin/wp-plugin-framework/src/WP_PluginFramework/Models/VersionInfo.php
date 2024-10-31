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

namespace WP_PluginFramework\Models;

defined( 'ABSPATH' ) || exit;

use WP_PluginFramework\PluginContainer;
use WP_PluginFramework\Utils\DebugLogger;

class VersionInfo extends OptionModel
{
    const PLUGIN_SLUG = 'plugin_slug';
    const PLUGIN_NAME = 'plugin_name';
    const PLUGIN_VERSION = 'plugin_version';
    const WP_PLUGIN_FRAMEWORK_NAME = 'wp_plugin_framework_name';
    const WP_PLUGIN_FRAMEWORK_VERSION = 'wp_plugin_framework_version';
    const INSTALL_DATE_TIME = 'install_date_time';
    const UPGRADE_DATE_TIME = 'upgrade_date_time';
    const ACTIVATED_DATE_TIME = 'activated_date_time';
    const DEACTIVATED_DATE_TIME = 'deactivated_date_time';
    const UNINSTALLED_DATE_TIME = 'uninstall_date_time';
    const PLUGIN_INSTALL_STATE = 'plugin_install_state';

    const PLUGIN_INSTALL_STATE_ACTIVATED = 'activated';
    const PLUGIN_INSTALL_STATE_DEACTIVATED = 'deactivated';
    const PLUGIN_INSTALL_STATE_UNINSTALLED = 'uninstalled';

    /* Metadata describing database fields and data properties: */
    static $MetaData = array
    (
        self::PLUGIN_SLUG => array(
            'data_type'     => 'StringType',
            'default_value' => '',
        ),
        self::PLUGIN_NAME => array(
            'data_type'     => 'StringType',
            'default_value' => '',
        ),
        self::PLUGIN_VERSION => array(
            'data_type'     => 'StringType',
            'default_value' => '',
        ),
        self::WP_PLUGIN_FRAMEWORK_NAME => array(
            'data_type'     => 'StringType',
            'default_value' => 'WP_Plugin_Framework'
        ),
        self::WP_PLUGIN_FRAMEWORK_VERSION => array(
            'data_type'     => 'StringType',
            'default_value' => PluginContainer::WP_PLUGIN_MVC_FRAMEWORK_VERSION
        ),
        self::INSTALL_DATE_TIME => array(
            'data_type'     => 'TimeStampType',
            'default_value' => 0
        ),
        self::UPGRADE_DATE_TIME => array(
            'data_type'     => 'TimeStampType',
            'default_value' => 0
        ),
        self::ACTIVATED_DATE_TIME => array(
            'data_type'     => 'TimeStampType',
            'default_value' => 0
        ),
        self::DEACTIVATED_DATE_TIME => array(
            'data_type'     => 'TimeStampType',
            'default_value' => 0
        ),
        self::UNINSTALLED_DATE_TIME => array(
            'data_type'     => 'TimeStampType',
            'default_value' => 0
        ),
        self::PLUGIN_INSTALL_STATE => array(
            'data_type'     => 'StringType',
            'default_value' => ''
        )
    );

    public function SaveVersionInfo($plugin_version, $framework_version, $plugin_slug, $plugin_name, $install_state=false)
    {
        $result = $this->LoadData();
        if ($result)
        {
            $problem = false;
            $previous_plugin_version = $this->GetData(VersionInfo::PLUGIN_VERSION);

            $current_state = $this->GetData(VersionInfo::PLUGIN_INSTALL_STATE);

            switch($current_state)
            {
                case VersionInfo::PLUGIN_INSTALL_STATE_ACTIVATED:
                    if($install_state === VersionInfo::PLUGIN_INSTALL_STATE_ACTIVATED)
                    {
                        DebugLogger::WriteDebugWarning('Plugin was not correctly deactivated last time.');
                        $problem = true;
                    }
                    if($install_state === VersionInfo::PLUGIN_INSTALL_STATE_UNINSTALLED)
                    {
                        DebugLogger::WriteDebugWarning('Plugin was not correctly deactivated before uninstall.');
                        $problem = true;
                    }
                    break;

                case VersionInfo::PLUGIN_INSTALL_STATE_DEACTIVATED:
                    if($install_state === VersionInfo::PLUGIN_INSTALL_STATE_DEACTIVATED)
                    {
                        DebugLogger::WriteDebugWarning('Plugin was not correctly activated last time.');
                        $problem = true;
                    }
                    break;

                case VersionInfo::PLUGIN_INSTALL_STATE_UNINSTALLED:
                    if($install_state === VersionInfo::PLUGIN_INSTALL_STATE_DEACTIVATED)
                    {
                        DebugLogger::WriteDebugWarning('Plugin was not correctly activated last time before deactivation.');
                        $problem = true;
                    }
                    break;

                default:
                    DebugLogger::WriteDebugWarning('Previous install state missing.');
                    $problem = true;
                    break;
            }

            if($problem)
            {
                $data_record = $this->GetDataRecord();
                foreach ($data_record as $key => $value)
                {
                    DebugLogger::WriteDebugNote('Previous version info: ' . $key . ': ' . $value);
                }
            }

            if($install_state === VersionInfo::PLUGIN_INSTALL_STATE_ACTIVATED)
            {
                if ( $plugin_version != $previous_plugin_version )
                {
                    $this->SetData( VersionInfo::UPGRADE_DATE_TIME, time() );
                }
            }
        }
        else
        {
            /* First time installed */
            $this->SetData(VersionInfo::INSTALL_DATE_TIME, time());
            $previous_plugin_version = false;
        }

        $this->SetData(VersionInfo::PLUGIN_SLUG, $plugin_slug);
        $this->SetData(VersionInfo::PLUGIN_NAME, $plugin_name);
        $this->SetData(VersionInfo::PLUGIN_VERSION, $plugin_version);
        $this->SetData(VersionInfo::WP_PLUGIN_FRAMEWORK_NAME, 'WP_Plugin_Framework');
        $this->SetData(VersionInfo::WP_PLUGIN_FRAMEWORK_VERSION, $framework_version);
        switch($install_state)
        {
            case VersionInfo::PLUGIN_INSTALL_STATE_ACTIVATED:
                $this->SetData(VersionInfo::ACTIVATED_DATE_TIME, time());
                break;
            case VersionInfo::PLUGIN_INSTALL_STATE_DEACTIVATED:
                $this->SetData(VersionInfo::DEACTIVATED_DATE_TIME, time());
                break;
            case VersionInfo::PLUGIN_INSTALL_STATE_UNINSTALLED:
                $this->SetData(VersionInfo::UNINSTALLED_DATE_TIME, time());
                break;
        }
        if($install_state)
        {
            $this->SetData(VersionInfo::PLUGIN_INSTALL_STATE, $install_state);
        }
        $this->SaveData();

        return $previous_plugin_version;
    }
}
