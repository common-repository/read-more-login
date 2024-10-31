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
use WP_PluginFramework\Base\BaseObject;
use WP_PluginFramework\DataTypes\DataType;
use WP_PluginFramework\Utils\DebugLogger;

abstract class Model extends BaseObject
{
    const PRIMARY_KEY = 'id';
    const VALIDATION_ERROR_REQUIRED_FIELD = 0;
    const VALIDATION_ERROR_INVALID = 1;

    static $MetaData;

    protected $DataObjects = array();
    /** @var array boolean Each array item indicates a DataObject has been touched. */
    protected $TouchedData = array();
    protected $ValidationErrors = array();
    /** @var string Name used for storage in database, files etc. */
    protected $ModelName;

    public function __construct($model_name = null)
    {
        parent::__construct(null);

        if(!$model_name)
        {
            $class_name = get_called_class();
            $class_name = substr($class_name, strrpos($class_name, '\\') + 1);

            $plugin_container = PluginContainer::Instance();
            $model_name = $plugin_container->GetPrefixedPluginSlug() . '_' . $class_name;
        }

        $this->ModelName = $model_name;

        $this->ClearAllData();
    }

    /*
     * @return false    Model data/table/option don't exist or has never been created.
     * @return integer  Number of data records loaded.
     */
    abstract protected function LoadDataRecord($condition);

    abstract public function LoadColumn($field_name_list);
    abstract protected function SaveDataIndex($index);

    public function Create()
    {
    }

    public function Remove()
    {
    }

    protected function GetMetaData($key)
    {
        if(array_key_exists($key, static::$MetaData)){
            return static::$MetaData[$key];
        } else {
            DebugLogger::WriteDebugError('Unknown model key ' . $key);
            return null;
        }
    }

    public function GetMetaDataList()
    {
        return static::$MetaData;
    }

    public function FieldNameExist($key)
    {
        if($key == static::PRIMARY_KEY){
            return true;
        }
        $metadata_list = $this->GetMetaDataList();
        return array_key_exists($key, $metadata_list);
    }

    /**
     * @param  $name string
     * @return string
     */
    protected function FormatDataTypeName($name)
    {
        $namespace_root = str_replace('\\Models', '', __NAMESPACE__);
        $data_type = $namespace_root.'\\DataTypes\\'.$name;
        return $data_type;
    }

    /**
     * @param $metadata array
     * @param $key string
     * @param $value
     * @return DataType
     */
    protected function CreateDataObject($metadata, $key, $value=null)
    {
        $data_type = $this->FormatDataTypeName($metadata['data_type']);
        $data_object = new $data_type($metadata, $key, $value);
        return $data_object;
    }

    protected function InitRecord($record, $metadata_list=null)
    {
        if($metadata_list == null){
            $metadata_list = $this->GetMetaDataList();
            $record[self::PRIMARY_KEY] = null;
        }

        foreach ($metadata_list as $meta_name => $metadata)
        {
            if($meta_name !== static::PRIMARY_KEY) {
                if(isset($metadata['default_value'])){
                    $default_value = $metadata['default_value'];
                } else {
                    $default_value = null;
                }
                $record[$meta_name] = $default_value;
            }else{
                DebugLogger::WriteDebugNote('Field name ' . self::PRIMARY_KEY . ' is reserved.');
            }
        }
        return $record;
    }

    public function InitDataIfEmpty()
    {
        if (count($this->DataObjects) == 0)
        {
            $this->AddDataRecord();
        }
    }

    public function InitDefault()
    {
        $this->ClearAllData();
        $this->AddDataRecord();
    }

    public function ClearData()
    {
        if (count($this->DataObjects) > 0)
        {
            array_shift($this->DataObjects);
            array_shift($this->TouchedData);
        }
    }

    public function ClearAllData()
    {
        $this->DataObjects = array();
        $this->TouchedData = array();
    }

    public function GetDataTypeClass($key)
    {
        $metadata = $this->GetMetaData($key);
        if (isset($metadata))
        {
            $data_type_class = $this->FormatDataTypeName($metadata['data_type']);
        }
        else
        {
            DebugLogger::WriteDebugError('No metadata for key ' . $key);
            $data_type_class = null;
        }

        return $data_type_class;
    }

    public function GetDataType($key)
    {
        $data_type = null;

        if($key == static::PRIMARY_KEY){
            return 'integer';
        }
        else
        {
            $data_type_class = $this->GetDataTypeClass($key);
            if (isset($data_type_class))
            {
                $data_type = call_user_func(array($data_type_class, 'GetType'));
                if ($data_type == null)
                {
                    DebugLogger::WriteDebugError('No data type for key ' . $key);
                }
            }
            else
            {
                DebugLogger::WriteDebugError('No data type class for key ' . $key);
            }
        }

        return $data_type;
    }

    public function FilterExistingData($data_record)
    {
        $existing_data_record = array();

        foreach($data_record as $key => $value)
        {
            if ($this->FieldNameExist($key))
            {
                $existing_data_record[$key] = $value;
            }
        }

        return $existing_data_record;
    }

    public function ValidateDataRecord($data_record)
    {
        $result = true;
        foreach ($data_record as $key => $value)
        {
            if(!$this->ValidateData($key, $value))
            {
                $result = false;
            }
        }
        return $result;
    }

    public function ValidateData($key, $value)
    {
        if((!isset($value)) or ($value === ''))
        {
            if($this->IsRequiredField($key))
            {
                $this->AddValidateError($key, static::VALIDATION_ERROR_REQUIRED_FIELD);
                return false;
            }
        }

        $metadata = $this->GetMetaData($key);
        $data_object = $this->CreateDataObject($metadata, $key);
        if(isset($data_object))
        {
            if ($data_object->Validate($value))
            {
                return true;
            }
            else
            {
                $errors = $data_object->GetValidateErrors();
                if (isset($errors))
                {
                    $this->AddValidateError($key, $errors);
                }
                else
                {
                    $this->AddValidateError($key, static::VALIDATION_ERROR_INVALID);
                }
            }
        }

        return false;
    }

    public function AddValidateError($key, $errors)
    {
        $this->SetPropertyKeyValues('ValidationErrors', $key, $errors);
    }

    public function GetValidateErrors($key=null)
    {
        if(isset($key))
        {
            return $this->GetPropertyKeyValues('ValidationErrors', $key);
        }
        else
        {
            return $this->GetProperty('ValidationErrors');
        }
    }

    public function IsRequiredField($key)
    {
        $metadata = $this->GetMetaData($key);
        if(isset($metadata))
        {
            if(isset($metadata['required']))
            {
                return $metadata['required'];
            }
        }

        return null;
    }

    public function CheckDataType($key, $value)
    {
        if ($this->FieldNameExist($key)) {
            $value_data_type = gettype($value);
            if($value_data_type === 'NULL'){
                // TODO Should implement check if null is allowed data type.
                return true;
            }
            $meta_data_type = $this->GetDataType($key);
            if(($meta_data_type == 'double') and ($value_data_type == 'integer')) {
                return true;
            }
            if ($value_data_type == $meta_data_type) {
                return True;
            } else {
                return false;
            }
        }
        else
        {
            DebugLogger::WriteDebugError('Invalid key ' . $key);
            return false;
        }
    }

    public function TouchData()
    {
        $this->TouchedData[0] = True;

    }

    public function TouchAllData()
    {
        for($index=0; $index< count($this->DataObjects); $index++) {
            $this->TouchedData[$index] = true;
        }
    }

    protected function AddDataRecord($data_list=null)
    {
        $new_data_record = array();
        $new_data_record = $this->InitRecord($new_data_record);

        $new_data_record = array($new_data_record);
        $this->DataObjects = array_merge($this->DataObjects, $new_data_record);
        $this->TouchedData[] = false;

        $index = count($this->DataObjects) - 1;

        if($data_list != null) {
            $this->SetDataRecordIndexed($index, $data_list);
        }

        return $index;
    }

    public function SetDataRecord($data_record)
    {
        $this->SetDataRecordIndexed(0, $data_record);

        return true;
    }

    protected function SetDataRecordIndexed($index, $data_record)
    {
        foreach($data_record as $key => $value){
            $this->SetDataIndex($index, $key, $value);
        }
    }

    public function SetDataIndex($index, $key, $value)
    {
        $this->InitDataIfEmpty();
        if ($this->CheckDataType($key, $value))
        {
            $this->DataObjects[$index][$key] = $value;
            $this->TouchedData[$index] = true;
        }
        else
        {
            /* Create a data object and try set value using that object. Maybe it has conversation function */
            $metadata = $this->GetMetaData($key);
            if(isset($metadata))
            {
                $data_object = $this->CreateDataObject($metadata, $key, $value);
                if (method_exists($data_object, 'SetValue'))
                {
                    $data_object->SetValue($value);
                    $normalized_value = $data_object->GetValue();
                    $this->DataObjects[$index][$key] = $normalized_value;
                    $this->TouchedData[$index] = true;
                }
                else
                {
                    DebugLogger::WriteDebugError('Invalid value type ' . gettype($value) . ' for ' . $key);
                }
            }
        }
    }

    public function SetData($key, $value)
    {
        $this->SetDataIndex(0, $key, $value);

    }

    public function SetAllData($key, $value)
    {
        for($index=0; $index< count($this->DataObjects); $index++) {
            $this->SetDataIndex(0, $key, $value);
        }
    }

    public function GetDataRecord()
    {
        return $this->GetDataRecordIndexed(0);
    }

    protected function GetDataRecordIndexed($index)
    {
        return $this->DataObjects[$index];
    }

    public function GetData($key)
    {
        return $this->GetDataIndex(0, $key);
    }

    public function GetDataIndex($index, $key)
    {
        if ($this->FieldNameExist($key)) {
            if($index < count($this->DataObjects))
            {
                return $this->DataObjects[$index][$key];
            }
            else
            {
                return null;
            }
        }
        else
        {
            DebugLogger::WriteDebugError('Invalid key ' . $key);
        }

        return null;
    }

    public function GetDataObjectRecord()
    {
        return $this->GetDataObjectRecordIndexed(0);
    }

    public function GetDataObjectRecordIndexed($index)
    {
        $data_obj_record = array();
        $metadata_list = $this->GetMetaDataList();
        foreach($metadata_list as $key => $metadata)
        {
            $data_obj_record[$key] = $this->GetDataObjectIndex($index, $key);
        }

        return $data_obj_record;
    }

    public function GetDataObject($key)
    {
        return $this->GetDataObjectIndex(0, $key);
    }

    public function GetDataObjectIndex($index, $key)
    {
        if ($this->FieldNameExist($key)) {
            $value =  $this->DataObjects[$index][$key];
            $metadata = $this->GetMetaData($key);
            $data_object = $this->CreateDataObject($metadata, $key, $value);
            return $data_object;
        }
        else
        {
            DebugLogger::WriteDebugError('Invalid key ' . $key);
        }

        return null;
    }

    public function SortData($key, $sort_order, $sort_flag)
    {
        if($this->GetDataCount() > 1) {
            if ($this->FieldNameExist($key)) {
                foreach ($this->DataObjects as $i => $row) {
                    $orders[$i] = $row[$key];
                }
            }

            array_multisort($orders, $sort_order, $sort_flag, $this->DataObjects);
        }
    }

    public function GetCopyAllData()
    {
        // TODO May have to clone this
        return $this->DataObjects;
    }

    public function GetDataCount()
    {
        return count($this->DataObjects);
    }

    public function FetchData()
    {
        if (count($this->DataObjects)) {
            array_shift($this->DataObjects);
            array_shift($this->TouchedData);
        }
    }

    public function LoadData($conditions=null, $value=null)
    {
        if (gettype($conditions) == 'string')
        {
            $field = $conditions;

            $conditions = array();
            $conditions[] = array(
                'field' => $field,
                'value' => $value
            );
        }

        $this->ClearAllData();
        return $this->LoadMoreData($conditions);
    }

    public function LoadAllData()
    {
        $this->ClearAllData();
        $condition = null;
        $records_loaded = $this->LoadDataRecord($condition);

        if($records_loaded)
        {
            for ( $index = 0; $index < $records_loaded; $index ++ )
            {
                $this->TouchedData[] = false;
            }
        }

        return $records_loaded;
    }

    public function LoadMoreData($conditions)
    {
        if(isset($conditions))
        {
            foreach ($conditions as $condition)
            {
                $where_field = $condition['field'];
                $where_value = $condition['value'];
                if (!$this->CheckDataType($where_field, $where_value))
                {
                    DebugLogger::WriteDebugError('Wrong data type for key=' . $where_field . ' value=' . $where_value);
                }
            }
        }

        return $this->LoadDataRecord($conditions);
    }

    public function SaveData()
    {
        return $this->SaveDataIndex(0);
    }

    public function SaveAllData()
    {
        $err = false;
        for($index=0; $index< count($this->DataObjects); $index++) {
            if ($this->SaveDataIndex($index) == false) {
                $err = true;
            }
        }

        if ($err) {
            return false;
        }
        else {
            return true;
        }
    }

}
