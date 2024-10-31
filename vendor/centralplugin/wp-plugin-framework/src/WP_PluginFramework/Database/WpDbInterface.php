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

namespace WP_PluginFramework\Database;

defined( 'ABSPATH' ) || exit;

use WP_PluginFramework\Utils\DebugLogger;

class WpDbInterface
{
    const WHERE_EQUAL = '=';
    const WHERE_NOT_EQUAL = '!=';
    const WHERE_GREATER = '>';
    const WHERE_GREATER_EQUAL = '>=';
    const WHERE_LESS = '<';
    const WHERE_LESS_EQUAL = '<=';

    private $PrepareError = false;
    private $LastQueryResult = null;

    public function __construct()
    {
    }

    public function Read($table_name, $select_columns, $where=null)
    {
        $safe_prefixed_table_name = $this->PrepareTableName($table_name);
        $safe_select_columns = $this->PrepareSqlSelect($select_columns);

        $sql = 'SELECT ' . $safe_select_columns . ' FROM `' . $safe_prefixed_table_name . '`';

        if(isset($where))
        {
            $safe_where = $this->PrepareSqlWhere($where);
            $sql .= ' WHERE ' . $safe_where;
        }

        if($this->QueryGetResult($sql))
        {
            return $this->LastQueryResult;
        }
        else
        {
            return null;
        }
    }

    public function Write($table_name, $data_array)
    {
        $safe_prefixed_table_name = $this->PrepareTableName($table_name);

        /* QueryUpdate will do the sql escape */
        if($this->QueryInsert($safe_prefixed_table_name, $data_array))
        {
            return $this->LastQueryResult;
        }
        else
        {
            return null;
        }
    }

    public function Update($table_name, $where, $data)
    {
        $safe_prefixed_table_name = $this->PrepareTableName($table_name);

        /* QueryUpdate will do the sql escape */
        return $this->QueryUpdate($safe_prefixed_table_name, $data, $where);
    }

    public function DeleteTableRecord($table_name, $condition)
    {
        $safe_prefixed_table_name = $this->PrepareTableName($table_name);

        /* QueryUpdate will do the sql escape */
        return $this->QueryDelete($safe_prefixed_table_name, $condition);
    }

    public function CreateTable($table_name, $meta_data_list, $primary_key)
    {
        $safe_prefixed_table_name = $this->PrepareTableName($table_name);
        $safe_meta_data_list = $this->PrepareDataValue($meta_data_list,false, 2);
        $safe_primary_key = $this->PrepareDataValue($primary_key,true);

        $safe_fields = '';
        foreach ($safe_meta_data_list as $safe_field_name => $safe_meta)
        {
            $safe_db_type = $safe_meta['db_type'];

            if ($safe_fields !== '')
            {
                $safe_fields .= ',';
            }

            $safe_fields .= '`' . $safe_field_name . '` ' . $safe_db_type;

            if ($safe_field_name == $safe_primary_key)
            {
                $safe_fields .= ' AUTO_INCREMENT';
            }

            if(isset($safe_meta['db_collation']))
            {
                $safe_fields .= ' COLLATE `'  . $safe_meta['db_collation'] . '`';
            }
        }

        if ($safe_primary_key)
        {
            $safe_fields .= ',PRIMARY KEY (`' . $safe_primary_key . '`)';
        }

        $sql = 'CREATE TABLE `' . $safe_prefixed_table_name . '` (' . $safe_fields . ')';

        return $this->QueryGetResult($sql);
    }

    public function RemoveTable($table_name)
    {
        $safe_prefixed_table_name = $this->PrepareTableName($table_name);

        $sql = 'DROP TABLE `' . $safe_prefixed_table_name . '`';

        return $this->QueryGetResult($sql);
    }

    public function CreateTableField($table_name, $column_list, $column_type_list, $column_default_value=null, $column_location=null)
    {
        $safe_prefixed_table_name = $this->PrepareTableName($table_name);
        $safe_column_list = $this->PrepareKeyValue($column_list, true, 1);
        $safe_colum_type_list = $this->PrepareDataValue($column_type_list, true, 1);
        $safe_column_default_value = $this->PrepareDataValue($column_default_value, false, 1);
        $safe_column_location = $this->PrepareKeyValue($column_location, false);

        $sql = 'ALTER TABLE `' . $safe_prefixed_table_name . '` ADD COLUMN `' . $safe_column_list . '` ' . $safe_colum_type_list;

        if ($safe_column_default_value)
        {
            $sql .= ' DEFAULT `' . $safe_column_default_value . '`';
        }

        if ($safe_column_location)
        {
            $sql .= ' AFTER `' . $safe_column_location . '`';
        }

        return $this->QueryGetResult($sql);
    }

    public function ChangeTableRenameField($table_name, $column, $new_column, $colum_data_type)
    {
        $safe_prefixed_table_name = $this->PrepareTableName($table_name);
        $safe_field_name = $this->PrepareKeyValue($column);
        $safe_new_field_name = $this->PrepareKeyValue($new_column);
        $safe_db_field_type = $this->PrepareDataValue($colum_data_type);

        $sql = 'ALTER TABLE `' . $safe_prefixed_table_name . '` CHANGE COLUMN `' . $safe_field_name . '` `' . $safe_new_field_name . '` ' . $safe_db_field_type;

        return $this->QueryGetResult($sql);
    }

    public function DeleteTableColumn($table_name, $column)
    {
        $safe_prefixed_table_name = $this->PrepareTableName($table_name);
        $safe_column = $this->PrepareKeyValue($column);

        $sql = 'ALTER TABLE `' . $safe_prefixed_table_name . '` DROP COLUMN `' . $safe_column . '`';

        return $this->QueryGetResult($sql);
    }

    public function TableExist($table_name)
    {
        $safe_prefixed_table_name = $this->PrepareTableName($table_name);

        if($this->QueryGetResult('SHOW TABLES'))
        {
            foreach ($this->LastQueryResult as $table_entry)
            {
                foreach ($table_entry as $key => $table_name)
                {
                    if ($table_name === $safe_prefixed_table_name)
                    {
                        return true;
                    }
                }
            }
        }
        else
        {
            return false;
        }
    }

    public function GetTableDescription($table_name)
    {
        $safe_prefixed_table_name = $this->PrepareTableName($table_name);

        $sql = 'DESCRIBE `' . $safe_prefixed_table_name . '`';

        if($this->QueryGetResult($sql))
        {
            return $this->LastQueryResult;
        }
        else
        {
            return null;
        }
    }

    private function QueryInsert($safe_prefixed_table_name, $safe_data_array)
    {
        $this->LastQueryResult = null;

        if (!$this->PrepareError)
        {
            global $wpdb;

            $this->LastQueryResult = $wpdb->insert($safe_prefixed_table_name, $safe_data_array);

            if ($wpdb->last_error)
            {
                $this->LastQueryResult = null;
                DebugLogger::WriteDebugError('Database insert error:' . $wpdb->last_error);
            }
            else
            {
                $this->LastQueryResult = $wpdb->insert_id;
                return true;
            }
        }
        else
        {
            DebugLogger::WriteDebugError('Database could not insert table ' . $safe_prefixed_table_name);
        }

        return false;
    }

    private function QueryUpdate($safe_prefixed_table_name, $safe_data, $safe_condition)
    {
        $this->LastQueryResult = null;

        if (!$this->PrepareError)
        {
            global $wpdb;

            $this->LastQueryResult = $wpdb->update($safe_prefixed_table_name, $safe_data, $safe_condition);

            if ($wpdb->last_error)
            {
                DebugLogger::WriteDebugError('Database delete error:' . $wpdb->last_error);
            }
            else
            {
                return true;
            }
        }
        else
        {
            DebugLogger::WriteDebugError('Database could not update table ' . $safe_prefixed_table_name);
        }

        return false;
    }

    private function QueryDelete($safe_prefixed_table_name, $safe_condition)
    {
        $this->LastQueryResult = null;

        if (!$this->PrepareError)
        {
            global $wpdb;

            $this->LastQueryResult = $wpdb->delete($safe_prefixed_table_name, $safe_condition);

            if($this->LastQueryResult === false)
            {
                DebugLogger::WriteDebugError('Database delete failed.');
            }

            if($this->LastQueryResult === 0)
            {
                DebugLogger::WriteDebugError('Database delete no data.');
            }

            if ($wpdb->last_error)
            {
                DebugLogger::WriteDebugError('Database delete error:' . $wpdb->last_error);
            }
            else
            {
                return true;
            }
        }
        else
        {
            DebugLogger::WriteDebugError('Database could not delete column ' . $safe_condition . ' in table ' . $safe_prefixed_table_name);
        }

        return false;
    }

    private function QueryGetResult($sql)
    {
        $this->LastQueryResult = null;

        if (!$this->PrepareError)
        {
            global $wpdb;

            $this->LastQueryResult = $wpdb->get_results($sql, ARRAY_A);

            if ($wpdb->last_error)
            {
                DebugLogger::WriteDebugError('Database error:' . $wpdb->last_error . 'in query "' . $sql . '"');
            }
            else
            {
                return true;
            }
        }
        else
        {
            DebugLogger::WriteDebugError('Database could not query ' . $sql);
        }

        return false;
    }


    private function PrepareTableName($table_name)
    {
        if(isset($table_name) and is_string($table_name))
        {
            global $wpdb;

            $prefixed_table_name = $wpdb->prefix . $table_name;
            $safe_prefixed_table_name = $this->PrepareKeyValue($prefixed_table_name);
            return $safe_prefixed_table_name;
        }
        else
        {
            DebugLogger::WriteDebugError('Table name missing.');
            $this->PrepareError = true;
            return null;
        }
    }

    private function PrepareSqlSelect($select_columns)
    {
        if (is_string($select_columns) and ($select_columns === '*'))
        {
            $safe_select_column_str = '*';
        }
        else
        {
            $safe_select_columns = $this->PrepareKeyValue($select_columns, true, 1);

            $safe_select_column_str = '';
            if (is_array($safe_select_columns))
            {
                foreach ($safe_select_columns as $safe_select_column)
                {
                    if ($safe_select_column_str)
                    {
                        $safe_select_column_str .= ',';
                    }

                    $safe_select_column_str .= '`' . $safe_select_column . '`';
                }
            }
            else if (is_string($safe_select_columns))
            {
                if ($safe_select_columns === '')
                {
                    $safe_select_column_str = '*';
                }
                else
                {
                    $safe_select_column_str .= '`' . $safe_select_columns . '`';
                }
            }
        }
        return $safe_select_column_str;
    }

    private function PrepareSqlWhere($where, $to_string=true)
    {
        $where_str = '';

        $safe_where = $this->PrepareKeyValue($where, true, 2);

        if($to_string)
        {
            if ($safe_where)
            {
                $more_than_one_where = false;
                $defined_comparators = array(
                    self::WHERE_EQUAL,
                    self::WHERE_NOT_EQUAL,
                    self::WHERE_GREATER,
                    self::WHERE_GREATER_EQUAL,
                    self::WHERE_LESS,
                    self::WHERE_LESS_EQUAL
                );

                foreach ($safe_where as $safe_key => $safe_where_item)
                {
                    if (is_array($safe_where_item))
                    {
                        $where_field = $safe_where_item['field'];
                        $where_value = $safe_where_item['value'];
                    }
                    else
                    {
                        $where_field = $safe_key;
                        $where_value = $safe_where_item;
                    }

                    if (isset($safe_where_item['comparator']))
                    {
                        if (in_array($safe_where_item['comparator'], $defined_comparators, true))
                        {
                            $comparator = $safe_where_item['comparator'];
                        }
                        else
                        {
                            DebugLogger::WriteDebugError('Invalid comparator "' . $safe_where_item['comparator'] . '"');
                            $this->PrepareError = true;
                            return null;
                        }
                    }
                    else
                    {
                        $comparator = '=';
                    }

                    if ($more_than_one_where)
                    {
                        $where_str .= ' AND ';
                    }

                    $where_str .= "`" . $where_field . "`" . $comparator . "'" . $where_value . "'";

                    $more_than_one_where = true;
                }
            }
            return $where_str;
        }
        else
        {
            return $safe_where;
        }
    }

    private function PrepareKeyValue($value, $mandatory_value=true, $max_array_level=0, $mandatory_array_value=false)
    {
        return $this->PrepareValue($value, true, $mandatory_value, $max_array_level, $mandatory_array_value=false);
    }

    private function PrepareDataValue($value, $mandatory_value=true, $max_array_level=0, $mandatory_array_value=false)
    {
        return $this->PrepareValue($value, false, $mandatory_value, $max_array_level, $mandatory_array_value=false);
    }

    private function PrepareValue($value, $is_key_value, $mandatory_value, $max_array_level, $mandatory_array_value)
    {
        if(isset($value))
        {
            $value_type = gettype($value);
            switch ($value_type)
            {
                case 'array':
                    if($max_array_level > 0)
                    {
                        $max_array_level--;

                        $safe_array = array();
                        foreach ($value as $array_key => $array_value)
                        {
                            if(!isset($array_value))
                            {
                                if ($mandatory_array_value)
                                {
                                    DebugLogger::WriteDebugError('Prepare missing array values.');
                                    $this->PrepareError = true;
                                }
                            }
                            $safe_array_value = $this->PrepareValue($array_value, $is_key_value, $mandatory_value, $max_array_level, $mandatory_array_value);

                            if (is_integer($array_key))
                            {
                                $safe_array[$array_key] = $safe_array_value;
                            }
                            else if (is_string($array_key))
                            {
                                $safe_array_key = $this->PrepareValue($array_key, $mandatory_value, $is_key_value, $max_array_level, $mandatory_array_value);
                                if ($safe_array_key)
                                {
                                    $safe_array[$safe_array_key] = $safe_array_value;
                                }
                                else
                                {
                                    DebugLogger::WriteDebugError('Database prepare error. key error: ' . $safe_array_key);
                                    $this->PrepareError = true;
                                    return null;
                                }
                            }
                            else
                            {
                                DebugLogger::WriteDebugError('Database prepare error. invalid object: ' . gettype($array_key));
                                $this->PrepareError = true;
                                return null;
                            }
                        }
                        return $safe_array;
                    }
                    else
                    {
                        DebugLogger::WriteDebugError('Database prepare error. Invalid array level.');
                        $this->PrepareError = true;
                        return null;
                    }
                    break;

                case 'string':
                    $safe_value = esc_sql($value);

                    if($is_key_value)
                    {
                        $value_string = strval($value);
                        if ($safe_value === $value_string)
                        {
                            if(!preg_match('/^[A-Za-z0-9_\-]+$/', $safe_value))
                            {
                                DebugLogger::WriteDebugError('Database prepare invalid characters: ' . $safe_value);
                                $this->PrepareError = true;
                            }
                        }
                        else
                        {
                            DebugLogger::WriteDebugError('Database prepare error: ' . $safe_value);
                            $this->PrepareError = true;
                        }
                    }
                    return $safe_value;
                    break;

                case 'integer':
                    return $value;
                    break;

                default:
                    DebugLogger::WriteDebugError('Unsupported type: ' . $value_type);
                    $this->PrepareError = true;
                    break;
            }
        }
        else
        {
            if($mandatory_value === true)
            {
                DebugLogger::WriteDebugError('Prepare missing value');
                $this->PrepareError = true;
            }
        }

        return null;
    }
}
