<?php
namespace ounun\cache\drive;

abstract class base
{
    /**
     * @var string 模块名称
     */
    protected $_mod;

    /**
     * 设定数据keys
     * @param $key
     */
    abstract public function key($key);

    /**
     * 设定数据Value
     * @param $val
     */
    abstract public function val($val);

    /**
     * 读取数据
     * @param $keys
     * @return mixed|null
     */
    abstract public function read();

    /**
     * 写入已设定的数据
     * @return bool
     */
    abstract public function write();

    /**
     * 读取数据中$key的值
     * @param $sub_key
     */
    abstract public function get($sub_key);

    /**
     * 设定数据中$sub_key为$sub_val
     * @param $sub_key
     * @param $sub_vals
     */
    abstract public function set($sub_key, $sub_val);

    /**
     * 删除数据
     * @return bool
     */
    abstract public function delete();

    /**
     * 取得 File:文件名  Memcache|Redis:缓存KEY
     * @return string
     */
    abstract public function filename();

    /**
     * 取得 mod:名称
     * @return string
     */
    abstract public function mod();
}
