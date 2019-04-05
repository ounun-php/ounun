<?php

namespace ounun\cache\drive;

class redis extends base
{
    /** @var array Redis服务器配制 */
    private $_redis_config = array();

    /** @var \Redis */
    private $_redis = null;

    /** @var int */
    private $_expire = 0;

    /** @var bool false:混合数据 true:字符串 */
    private $_format_string = false;

    /** @var bool false:少量    true:大量 */
    private $_large_scale = false;

    /** @var string key */
    private $_key = null;

    /** @var array    数据 */
    private $_data = null;

    /** @var bool false:没读    true:已读 */
    private $_is_read = false;

    /**
     * _cache_redis constructor.
     * @param string $mod
     * @param int $expire
     * @param bool $large_scale
     * @param bool $format_string
     */
    public function __construct($mod = 'def', $expire = 0, $large_scale = false, $format_string = false, $auth = null)
    {
        $this->_mod = $mod;
        $this->_redis_config = array();
        $this->_redis = null;
        $this->_auth = $auth;

        /** @var int */
        $this->_expire = $expire;
        $this->_large_scale = $large_scale;
        $this->_format_string = $format_string;
    }

    /**
     * 设定Redis服务器
     * @param array $servers array(['host','port'],['host','port'],...)
     * @return bool
     */
    public function connect($host, $port)
    {
        $port = (int)$port;
        // config
        $this->_redis_config[] = array('host' => $host, 'port' => $port);
        // addServer
        if (null == $this->_redis) {
            $this->_redis = new \Redis();
        }
        if ($host && $port) {
            $this->_redis->connect($host, $port);
            if ($this->_auth && $this->_auth['password']) {
                $this->_redis->auth($this->_auth['password']);
            }
        } else {
            trigger_error("ERROR! Redis::Arguments Error!.", E_USER_ERROR);
        }
    }

    /**
     * 设定数据keys
     * @param $key
     */
    public function key($key)
    {
        if ($this->_large_scale) {
            $key = md5($key);
        }
        if ($this->_format_string) {
            $this->_data = '';
            $this->_is_read = false;
        } else {
            $this->_data = null;
            $this->_is_read = false;
        }
        $this->_key = "{$this->_mod}.{$key}";
    }

    /**
     * 设定数据Value
     * @param $val
     */
    public function val($val)
    {
        $this->_is_read = true;
        $this->_data = $val;
    }

    /**
     * 读取数据
     * @param $keys
     * @return mixed|null
     */
    public function read()
    {
        if ($this->_is_read) {
            return $this->_data;
        }
        // read
        $this->_is_read = true;
        if ($this->_format_string) {
            $this->_data = $this->_redis->get($this->_key);
        } else {
            $str = $this->_redis->get($this->_key);
            $this->_data = unserialize($str);
        }
        return $this->_data;
    }

    /**
     * 写入已设定的数据
     * @return bool
     */
    public function write()
    {
        if (false == $this->_is_read) {
            trigger_error("ERROR! \$this->_data:null.", E_USER_ERROR);
        }
        if ($this->_format_string) {
            return $this->_redis->set($this->_key, $this->_data, $this->_expire);
        } else {
            $str = serialize($this->_data);
            return $this->_redis->set($this->_key, $str, $this->_expire);
        }
    }

    /**
     * 读取数据中$key的值
     * @param $sub_key
     */
    public function get($sub_key)
    {
        if ($this->_format_string) {
            trigger_error("ERROR! format_string.", E_USER_ERROR);
        }
        if (!$this->_is_read) {
            $this->read();
        }
        if ($this->_data) {
            return $this->_data[$sub_key];
        }
        return null;
    }

    /**
     * 设定数据中$sub_key为$sub_val
     * @param $sub_key
     * @param $sub_vals
     */
    public function set($sub_key, $sub_val)
    {
        if ($this->_format_string) {
            trigger_error("ERROR! format_string.", E_USER_ERROR);
        }
        if (!$this->_is_read) {
            $this->read();
        }
        if (!$this->_data) {
            $this->_data = array();
        }
        $this->_data[$sub_key] = $sub_val;
    }

    /**
     * 删除数据
     * @return bool
     */
    public function delete()
    {
        if ($this->_format_string) {
            $this->_data = '';
            $this->_is_read = false;
        } else {
            $this->_data = null;
            $this->_is_read = false;
        }
        return $this->_redis->delete($this->_key);
    }

    /**
     * 取得 File:文件名  Memcache|Redis:缓存KEY
     * @return string
     */
    public function filename()
    {
        return $this->_key;
    }

    /**
     * 取得 mod:名称
     * @return string
     */
    public function mod()
    {
        return $this->_mod;
    }
}