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

class OptionModel extends Model
{
    const OPTION_NAME = null;

    public function __construct($model_name = null)
    {
        if(!isset($model_name))
        {
            $model_name = static::OPTION_NAME;
        }

        parent::__construct($model_name);
    }

    public function Create()
    {
        $this->Initdefault();
        $data_record = $this->GetDataRecord();
        add_option($this->ModelName, $data_record);
        return $data_record;
    }

    public function Remove()
    {
        delete_option($this->ModelName);
    }

    protected function LoadDataRecord($condition)
    {
        $data_record = get_option($this->ModelName);

        if($data_record !== false)
        {
            $data_record_filtered = array();
            foreach ( $this->GetMetaDataList() as $key => $metadata )
            {
                if ( isset( $data_record[ $key ] ) )
                {
                    $data_record_filtered[ $key ] = $data_record[ $key ];
                }
            }

            $this->AddDataRecord( $data_record_filtered );

            /* Options are always only 1 record. */
            return 1;
        }
        else
        {
            /* Option don't exist. */
            return false;
        }
    }

    public function LoadColumn($field_name_list)
    {

    }

    protected function SaveDataIndex($index)
    {
        $data_record = $this->GetDataRecord();
        update_option($this->ModelName, $data_record);
        return true;
    }
}
