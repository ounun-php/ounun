<?php

namespace ounun\cache\drive;


class memcache extends base
{
    /** @var array Memcache服务器配制 */
    private $_mem_config = array();

    /** @var \Memcache */
    private $_mem = null;

    /** @var int */
    private $_expire = 0;

    /** @var bool false:混合数据 true:字符串 */
    private $_format_string = false;

    /** @var bool false:少量    true:大量 */
    private $_large_scale = false;

    /** @var int */
    private $_zip_threshold = 5000; // 5k

    /** @var int */
    private $_zip_min_saving = 0.3;  // 30%

    /** @var int */
    private $_flag = MEMCACHE_COMPRESSED;

    /** @var string key */
    private $_key = null;

    /** @var array    数据 */
    private $_data = null;

    /** @var bool false:没读    true:已读 */
    private $_is_read = false;

    /**
     * 设定Memcache服务器
     * @param array $servers array(['host','port','weight'],['host','port','weight'],...)
     * @return bool
     */
    public function __construct($mod = 'def', $expire = 0, $format_string = false, $large_scale = false, $zip_threshold = 5000, $zip_min_saving = 0.3, $flag = MEMCACHE_COMPRESSED)
    {
        $this->_mod = $mod;
        $this->_mem_config = array();
        $this->_mem = null;

        $this->_expire = $expire;
        $this->_large_scale = $large_scale;
        $this->_format_string = $format_string;
        $this->_zip_threshold = $zip_threshold;
        $this->_zip_min_saving = $zip_min_saving;
        $this->_flag = $flag;
    }

    /**
     * 设定Memcache服务器
     * @param array $servers array(['host','port','weight'],['host','port','weight'],...)
     * @return bool
     */
    public function add_server($host, $port, $weight)
    {
        $port = (int)$port;
        $weight = (int)$weight;
        // config
        $this->_mem_config[] = array('host' => $host, 'port' => $port, 'weight' => $weight);
        // addServer
        if (null == $this->_mem) {
            $this->_mem = new \Memcache();
        }
        if ($host && $port && $weight) {
            $this->_mem->addServer($host, $port, true, $weight);
            $this->_mem->setCompressThreshold($this->_zip_threshold, $this->_zip_min_saving);
        } else {
            trigger_error("ERROR! Memcache::Arguments Error!.", E_USER_ERROR);
        }
        if (!$this->_mem->getStats()) {
            trigger_error("ERROR! Memcache::getStats Error!.", E_USER_ERROR);
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
        $this->_data = $this->_mem->get($this->_key);
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
        return $this->_mem->set($this->_key, $this->_data, $this->_flag, $this->_expire);
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
            $this->_is_read = true;
        } else {
            $this->_data = null;
            $this->_is_read = true;
        }
        return $this->_mem->delete($this->_key);
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
