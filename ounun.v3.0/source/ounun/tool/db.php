<?php

namespace ounun\tool;


use ounun\pdo;

class db
{
    /** @var string 整数类型 */
    const Type_Int = 'int';
    /** @var string 浮点类型 */
    const Type_Float = 'float';
    /** @var string 布尔类型 */
    const Type_Bool = 'bool';
    /** @var string JSON类型 */
    const Type_Json = 'json';
    /** @var string 字符串类型 */
    const Type_String = 'string';

    /**
     * 数据库bind
     * @param array $bind_data    字段数据
     * @param array $data_default 默认字段数据
     * @param bool $is_update true:更新  false:插入
     * @param bool $is_update_default 数据插入 -> 本字段无效，
     *                                           数据更新 -> true:已默认字段数据为主  false:已字段数据为主
     * @return array
     */
    static public function bind(array $bind_data, array $data_default, bool $is_update = true, bool $is_update_default = false)
    {
        $bind = [];
        if ($is_update) {
            $fields = $is_update_default ? array_keys($data_default) : array_keys($bind_data);
        } else {
            $fields = array_keys($data_default);
        }
        foreach ($fields as $field) {
            $value = $data_default[$field];
            if ($value && isset($bind_data[$field])) {
                if (static::Type_Int == $value['type']) {
                    $bind[$field] = (int)$bind_data[$field];
                } elseif (static::Type_Float == $value['type']) {
                    $bind[$field] = (float)$bind_data[$field];
                } elseif (static::Type_Bool == $value['type']) {
                    $bind[$field] = $bind_data[$field] ? true : false;
                } elseif (static::Type_Json == $value['type']) {
                    $extend = $bind_data[$field];
                    if(is_array($extend) || is_object($extend)){
                        $bind[$field] = json_encode_unescaped($extend);
                    }else{
                        $extend = json_decode_array($bind_data[$field]);
                        if ($extend) {
                            $bind[$field] = json_encode_unescaped($extend);
                        }
                    }
                } else {
                    $bind[$field] = (string)$bind_data[$field];
                }
            } elseif ($value) {
                if (static::Type_Json == $value['type']) {
                    $bind[$field] = json_encode_unescaped($value['default']);
                } else {
                    $bind[$field] = $value['default'];
                }
            }
        }
        return $bind;
    }

    /**
     * @param pdo $db
     * @param string $table
     * @param string $field         字段名称 有 `
     * @param string|int $id
     * @param array $bind_data      字段数据
     * @param array $bind_default   默认字段数据
     * @param bool $is_update_force        只是数据更新
     * @param bool $is_update_default      数据插入 -> 本字段无效，数据更新 -> true:已默认字段数据为主  false:已字段数据为主
     * @param bool $is_not_auto_increment  只是数据插入(无自增长)
     * @param string $field2               字段名称 没有 `
     * @return array|int
     */
    static public function update(pdo $db, string $table, string $field, $id, array $bind_data, array $bind_default,
                                  bool $is_update_force = false, bool $is_update_default = false,
                                  bool $is_not_auto_increment = false,string $field2 = '')
    {
        $is_update = true;
        if ($id) {
            if ($is_update_force == false) {
                $is_update = $db->table($table)->is_repeat($field, $id);
            }
        } else {
            $is_update = false;
        }

        $bind = static::bind($bind_data, $bind_default, $is_update, $is_update_default);

        if ($is_update) {
            $modify_cc = $db->table($table)->where(" {$field} =:field  ", ['field' => $id])->update($bind);
            if ($modify_cc) {
                return $id;
            } else {
                return error("失败:更新数据库失败[".__LINE__."]\$table:{$table} \$id:{$id}");
            }
        } else {
            if($is_not_auto_increment){
                $field2 = $field2?$field2:str_replace(['`',' '],'',$field);
                if($id){
                    $bind[$field2] = $id;
                }else{
                    $id = $bind[$field2];
                }
                $db->table($table)->insert($bind);
                $modify_cc = $db->table($table)->is_repeat($field, $id);
                if ($modify_cc) {
                    return $id;
                } else {
                    return error("失败:插入数据库失败[".__LINE__."]\$table:{$table} \$id:{$id}");
                }
            }else{
                $id = $db->table($table)->insert($bind);
                if ($id) {
                    return $id;
                } else {
                    return error("失败:插入数据库失败[".__LINE__."]\$table:{$table} \$id:{$id}");
                }
            }
        }
    }
}