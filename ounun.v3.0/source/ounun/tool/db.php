<?php

namespace ounun\tool;


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
     * @param array $data 字段数据
     * @param array $data_default 默认字段数据
     * @param bool $is_update true:更新  false:插入
     * @param bool $is_field_base_default 数据插入 -> 本字段无效，
     *                                    数据更新 -> true:已默认字段数据为主  false:已字段数据为主
     * @return array
     */
    static public function bind(array $data, array $data_default, bool $is_update = true, bool $is_field_base_default = false)
    {
        $bind = [];
        if ($is_update) {
            $fields = $is_field_base_default ? array_keys($data_default) : array_keys($data);

        } else {
            $fields = array_keys($data_default);
        }
        foreach ($fields as $field) {
            $value = $data_default[$field];
            if ($value && isset($data[$field])) {
                if (static::Type_Int == $value['type']) {
                    $bind[$field] = (int)$data[$field];
                } elseif (static::Type_Float == $value['type']) {
                    $bind[$field] = (float)$data[$field];
                } elseif (static::Type_Bool == $value['type']) {
                    $bind[$field] = $data[$field] ? true : false;
                } elseif (static::Type_Json == $value['type']) {
                    $extend = $data[$field];
                    if(is_array($extend) || is_object($extend)){
                        $bind[$field] = json_encode_unescaped($extend);
                    }else{
                        $extend = json_decode_array($data[$field]);
                        if ($extend) {
                            $bind[$field] = json_encode_unescaped($extend);
                        }
                    }
                } else {
                    $bind[$field] = (string)$data[$field];
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
}