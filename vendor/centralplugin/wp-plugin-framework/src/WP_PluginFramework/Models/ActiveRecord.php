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

use WP_PluginFramework\Database\WpDbInterface;
use WP_PluginFramework\Utils\DebugLogger;

abstract class ActiveRecord extends Model
{
    const TABLE_NAME = null;

    protected $database = null;

    public function __construct($table_name=null)
    {
        $this->database = new WpDbInterface;

        if(!$table_name)
        {
            $table_name = static::TABLE_NAME;
        }

        parent::__construct($table_name);
    }

    protected function LoadDataRecord($conditions)
    {
        $records_loaded = false;

        $db_data_list = $this->database->Read($this->ModelName, '*', $conditions);
        if($db_data_list)
        {
            $records_loaded = count($db_data_list);
            for ($i = 0; $i < $records_loaded; $i++)
            {
                $data_list = array();
                foreach ($db_data_list[$i] as $key => $value)
                {
                    $data_list[$key] = $value;

                    $normal_type = $this->GetDataType($key);
                    if(isset($normal_type))
                    {
                        $data_type = gettype($value);
                        if ($normal_type != $data_type)
                        {

                            switch ($data_type)
                            {
                                case 'string':
                                    switch ($normal_type)
                                    {
                                        case 'integer':
                                            $normal_value = intval($value);
                                            $data_list[$key] = $normal_value;
                                            break;

                                        case 'double':
                                            $normal_value = floatval($value);
                                            $data_list[$key] = $normal_value;
                                            break;

                                        case 'boolean':
                                            $normal_value = intval($value);
                                            $data_list[$key] = $normal_value;
                                            break;

                                        default:
                                            DebugLogger::WriteDebugError('Unhandled data type ' . $normal_type);
                                    }
                                    break;

                                case 'NULL':
                                    $data_list[$key] = null;
                                    break;

                                default:
                                    DebugLogger::WriteDebugError('Unhandled data type ' . $data_type);
                            }
                        }
                    }
                    else
                    {
                        DebugLogger::WriteDebugError('Unknown column ' . $key . ' in ' . $this->ModelName);
                    }
                }
                $this->AddDataRecord($data_list);
            }
        }

        return $records_loaded;
    }

    public function LoadColumn($column_list)
    {
        $this->ClearAllData();
        $this->DataObjects = $this->database->Read($this->ModelName, $column_list);

        for($index=0; $index< count($this->DataObjects); $index++) {
            $this->TouchedData[] = false;
        }

        return count($this->DataObjects);
    }

    public function SaveDataIndex($index)
    {
        if ($this->TouchedData[$index]) {
            if ($this->DataObjects[$index][static::PRIMARY_KEY] == null) {
                $id = $this->database->Write($this->ModelName, $this->DataObjects[$index]);
                if ($id != null) {
                    $this->DataObjects[$index][static::PRIMARY_KEY] = $id;
                    return true;
                } else {
                    return false;
                }
            } else {
                $condition = [static::PRIMARY_KEY => strval($this->DataObjects[$index][static::PRIMARY_KEY])];
                return $this->database->Update($this->ModelName, $condition, $this->DataObjects[$index]);
            }
        }
        return false;
    }

    public function Delete()
    {
        if(count($this->DataObjects)) {
            if ($this->DataObjects[0][static::PRIMARY_KEY] != null) {
                $condition = [static::PRIMARY_KEY => $this->DataObjects[0][static::PRIMARY_KEY]];
                $this->database->DeleteTableRecord($this->ModelName, $condition);
                $this->ClearData();
            }
        }
    }

    public function ChangeFieldNameIndex($index, $field_name, $new_field_name)
    {
        $this->DataObjects[$index][$new_field_name] = $this->DataObjects[$index][$field_name];
        unset($this->DataObjects[$index][$field_name]);
        $this->TouchedData[$index] = true;

    }

    public function ChangeFieldName($field_name, $new_field_name)
    {
        $this->ChangeFieldNameIndex(0, $field_name, $new_field_name);
    }

    public function ChangeAllFieldName($field_name, $new_field_name)
    {
        for($index=0; $index< count($this->DataObjects); $index++) {
            $this->ChangeFieldNameIndex($index, $field_name, $new_field_name);
        }
    }

    public function Create()
    {
        if ($this->database->TableExist($this->ModelName))
        {
            $description_list = $this->database->GetTableDescription($this->ModelName);
            $previous_meta_field_name = null;
            $metadata_list = $this->GetMetaDataList();
            foreach($metadata_list as $meta_field_name => $metadata) {
                $data_object = $this->CreateDataObject($metadata, $meta_field_name);
                $meta_field_type = $data_object->GetDatabaseType();
                $field_found = false;

                foreach ($description_list as $description) {
                    $description_field_name = $description['Field'];
                    $description_field_type = $description['Type'];

                    if ($meta_field_name == $description_field_name) {
                        $field_found = true;
                        if (strtolower($meta_field_type) != strtolower($description_field_type)) {
                            DebugLogger::WriteDebugNote('Changing table "' . $this->ModelName . '" field "' . $meta_field_name . '" type from "' . strtoupper($description_field_type) . '" to "' . strtoupper($meta_field_type) . '"');

                            $now = current_time('timestamp', true);
                            $now_str = date('YmdHis', $now);

                            $temp_field_name = $meta_field_name . '_' . $now_str;

                            DebugLogger::WriteDebugNote('Create new temporary field "' . $temp_field_name . '"');
                            $this->database->CreateTableField($this->ModelName, $temp_field_name, $meta_field_type, $metadata['default_value'], $meta_field_name);

                            DebugLogger::WriteDebugNote('Convert old data from "' . $meta_field_name . '" to "' . $temp_field_name . '"...');
                            $this->LoadColumn([static::PRIMARY_KEY , $meta_field_name]);
                            $old_data = $this->GetCopyAllData();
                            $this->ChangeAllFieldName($meta_field_name, $temp_field_name);
                            $this->SaveAllData();

                            DebugLogger::WriteDebugNote('Compare data...');
                            $this->LoadColumn([static::PRIMARY_KEY , $temp_field_name]);
                            $converted_data = $this->GetCopyAllData();
                            $convert_error = false;
                            for ($i=0; $i < count($old_data); $i++) {
                                $old_data_str = strval($old_data[$i][$meta_field_name]);
                                $converted_data_str = strval($converted_data[$i][$temp_field_name]);
                                if ($old_data_str != $converted_data_str) {
                                    DebugLogger::WriteDebugError('Error converting data index=' . strval($i) . ' Old value "' . $old_data_str . " new value '" . $converted_data_str . "'");
                                    $convert_error = true;
                                }
                            }

                            if ($convert_error) {
                                $now = current_time('timestamp', true);
                                $now_str = date('YmdHis', $now);
                                $old_data_field_name = $meta_field_name . '_old_' . $now_str;
                                $this->database->ChangeTableRenameField($this->ModelName, $meta_field_name, $old_data_field_name);
                                $this->database->ChangeTableRenameField($this->ModelName, $temp_field_name, $meta_field_name);

                                DebugLogger::WriteDebugError('Error converting data in table "' . $this->ModelName . '"');
                                DebugLogger::WriteDebugError('Old data stored in field "' . $old_data_field_name . '"');
                                DebugLogger::WriteDebugError('Error converting data. Check table manually or retry.');
                            } else {
                                DebugLogger::WriteDebugNote('Data converted successfully.');
                                DebugLogger::WriteDebugNote('Delete old field "' . $meta_field_name . '"');
                                $this->database->DeleteTableColumn($this->ModelName, $meta_field_name);
                                DebugLogger::WriteDebugNote('Rename temporary field "' . $temp_field_name . '" to "' . $meta_field_name . '"');
                                $this->database->ChangeTableRenameField($this->ModelName, $temp_field_name, $meta_field_name, $meta_field_type);
                            }
                        }
                    }
                }

                if ($field_found == false) {
                    DebugLogger::WriteDebugNote('Warning: Changing table "' . $this->ModelName . '" add field "' . $meta_field_name . '" with type "' . strtoupper($meta_field_type) . '"');
                    $default_value = $data_object->GetDefaultValue();
                    $this->database->CreateTableField($this->ModelName, $meta_field_name, $meta_field_type, $default_value, $previous_meta_field_name);
                }

                $previous_meta_field_name = $meta_field_name;

            }
        }
        else {
            $data_type = $this->FormatDataTypeName('IdType');

            $data_object = new $data_type;

            $db_metadata = array(
                static::PRIMARY_KEY => array(
                    'db_type'     => $data_object->GetDatabaseType(),
                    'default_value' => null
                )
            );

            $metadata_list = $this->GetMetaDataList();
            foreach($metadata_list as $field_name => $metadata) {
                $data_object = $this->CreateDataObject($metadata, $field_name);

                $db_metadata[$field_name] = array(
                    'db_type'       => $data_object->GetDatabaseType(),
                    'default_value' => $data_object->GetDefaultValue(),
                    'db_collation'     => $data_object->GetDatabaseCollation()
                );
            }

            $this->database->CreateTable($this->ModelName, $db_metadata,self::PRIMARY_KEY);
        }
    }

    public function Remove()
    {
        $this->database->RemoveTable($this->ModelName);
    }
}
