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

use WP_PluginFramework\Utils\DebugLogger;

class Statistics
{
    static public function StatisticsPageView($post_id)
    {
        self::RegisterEvent($post_id, StatisticsDbTable::PAGE_VIEW);
    }

    static public function StatisticsRegister($post_id)
    {
        self::RegisterEvent($post_id, StatisticsDbTable::REGISTER);
    }

    static public function StatisticsVerifyEmail($post_id)
    {
        self::RegisterEvent($post_id, StatisticsDbTable::VERIFY);
    }

    static public function StatisticsCompleted($post_id)
    {
        self::RegisterEvent($post_id, StatisticsDbTable::COMPLETED);
    }

    static public function RegisterEvent($post_id, $event_type)
    {
        if (gettype($post_id) == 'integer')
        {
            $statistics_data = new StatisticsDbTable();
            if ($statistics_data->LoadData(StatisticsDbTable::POST_ID, $post_id))
            {
                $number = $statistics_data->GetData($event_type);
                $number = $number + 1;
                $statistics_data->SetData($event_type, $number);
                $statistics_data->SaveData();
            }
            else
            {
                $statistics_data->SetData(StatisticsDbTable::POST_ID, $post_id);
                $statistics_data->SetData($event_type, 1);
                $statistics_data->SaveData();
            }
        }
        else
        {
            DebugLogger::WriteDebugError(' invalid data type:', gettype($post_id));
        }
    }
}
