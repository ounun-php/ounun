<?php

namespace ounun\cache;

/**
 * 强制要求子类定义这些方法
 * Class _cache_base
 * @package ounun
 */
class core
{
    const Type_File = 1;
    const Type_Memcache = 2;
    const Type_Redis = 3;
    const Type_Memcached = 4;

    /**  @var \ounun\cache\drive\base */
    private $_drive = null;

    /** @var int 驱动类型  0:[错误,没设定驱动] 1:File 2:Memcache 3:Redis */
    protected $_type = 0;

    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->_type = 0;
    }

    /**
     * 设定 Cache配制
     * @param array $config Cache配制
     * $GLOBALS['scfg']['cache1'] = array
     * (
     * 'type'            => \ounun\Cache::Type_File,
     * 'mod'            => 'html',
     * 'root'            => Dir_Cache,
     * 'format_string' => false,
     * 'large_scale'    => true,
     * );
     * $GLOBALS['scfg']['cache2'] = array
     * (
     * 'type'          => \ounun\Cache::Type_Memcache,
     * 'mod'            => 'html',
     * 'sfg'           => array(array('host'=>'192.168.1.181','port'=>11211,'weight'=>100)),
     * 'zip_threshold' => 5000,
     * 'zip_min_saving'=> 0.3,
     * 'expire'        => (3600*24*30 - 3600),
     * 'flag'          => MEMCACHE_COMPRESSED,
     * 'format_string' => false,
     * 'large_scale'    => true,
     * );
     * $GLOBALS['scfg']['cache2'] = array
     * (
     * 'type'          => \ounun\Cache::Type_Memcached,
     * 'mod'            => 'html',
     * 'sfg'           => array(array('host'=>'192.168.1.181','port'=>11211,'weight'=>100)),
     * 'auth'          => array('username'=>'username','password'=>'password'),
     * 'expire'        => (3600*24*30 - 3600),
     * 'format_string' => false,
     * 'large_scale'    => true,
     * );
     * $GLOBALS['scfg']['cache3'] = array
     * (
     * 'type'            => \ounun\Cache::Type_Redis,
     * 'mod'            => 'html',
     * 'sfg'            => array(array('host'=>'192.168.1.181','port'=>6379)),
     * 'expire'        => (3600*24*30 - 3600),
     * 'format_string' => false,
     * 'large_scale'    => true,
     * );
     */
    public function config($config, $mod = null)
    {
        $mod = $mod ? $mod : $config['mod'];
        $type_list = [self::Type_File, self::Type_Memcache, self::Type_Memcached, self::Type_Redis];
        $type = in_array($config['type'], $type_list) ? $config['type'] : self::Type_File;
        if (self::Type_Redis == $type) {
            $sfg = $config['sfg'];
            $expire = $config['expire'];
            $auth = $config['auth'];
            $format_string = $config['format_string'];
            $large_scale = $config['large_scale'];
            $this->config_redis($sfg, $mod, $expire, $large_scale, $format_string, $auth);
        } elseif (self::Type_Memcache == $type) {
            $sfg = $config['sfg'];
            $zip_threshold = $config['zip_threshold'];
            $zip_min_saving = $config['zip_min_saving'];
            $expire = $config['expire'];
            $flag = $config['flag'];
            $format_string = $config['format_string'];
            $large_scale = $config['large_scale'];
            $this->config_memcache($sfg, $mod, $expire, $format_string, $large_scale, $zip_threshold, $zip_min_saving, $flag);
        } elseif (self::Type_Memcached == $type) {
            $sfg = $config['sfg'];
            $expire = $config['expire'];
            $auth = $config['auth'];
            $format_string = $config['format_string'];
            $large_scale = $config['large_scale'];
            $this->config_memcached($sfg, $mod, $expire, $format_string, $large_scale, $auth);
        } else //if(self::Type_File == $type)
        {
            $root = $config['root'];
            $format_string = $config['format_string'];
            $large_scale = $config['large_scale'];
            $this->config_file($mod, $root, $format_string, $large_scale);
        }
    }

    /**
     * 设定 file Cache配制
     * @param string $mod
     * @param $root
     * @param bool|false $large_scale
     */
    public function config_file($mod = 'def', $root = '', $format_string = false, $large_scale = false)
    {
        if (0 == $this->_type) {
            $this->_type = self::Type_File;
            $this->_drive = new drive\file($mod, $root, $format_string, $large_scale);
        } else {
            trigger_error("ERROR! Repeat Seting:Cache->config_file().", E_USER_ERROR);
        }
    }

    /**
     * 设定Memcache服务器
     * @param array $servers array(['host','port','weight'],['host','port','weight'],...)
     * @return bool
     */
    public function config_memcache(array $servers, $mod = 'def', $expire = 0, $format_string = false, $large_scale = false, $zip_threshold = 5000, $zip_min_saving = 0.3, $flag = MEMCACHE_COMPRESSED)
    {
        if (0 == $this->_type) {
            $this->_type = self::Type_Memcache;
            $this->_drive = new drive\memcache($mod, $expire, $format_string, $large_scale, $zip_threshold, $zip_min_saving, $flag);
            if (is_array($servers)) {
                foreach ($servers as $v) {
                    $this->_drive->add_server($v['host'], $v['port'], $v['weight']);
                }
            }
        } else {
            trigger_error("ERROR! Repeat Seting:Cache->config_memcache().", E_USER_ERROR);
        }
    }

    /**
     * 设定Memcache服务器
     * @param array $servers array(['host','port'],['host','port'],...)
     * @return bool
     */
    public function config_redis(array $servers, $mod = 'def', $expire = 0, $format_string = false, $large_scale = false, $auth = false)
    {
        if (0 == $this->_type) {
            $this->_type = self::Type_Redis;
            $this->_drive = new drive\redis($mod, $expire, $large_scale, $format_string, $auth);
            if (is_array($servers)) {
                foreach ($servers as $v) {
                    $this->_drive->connect($v['host'], $v['port']);
                }
            }
        } else {
            trigger_error("ERROR! Repeat Seting:Cache->config_redis().", E_USER_ERROR);
        }
    }

    /**
     * 设定Memcached服务器
     * @param array $servers array(['host','port','weight'],['host','port','weight'],...)
     * @return bool
     */
    public function config_memcached(array $servers, $mod = 'def', $expire = 0, $format_string = false, $large_scale = false, $auth = false)
    {
        if (0 == $this->_type) {
            $this->_type = self::Type_Memcached;
            $this->_drive = new drive\memcached($mod, $expire, $format_string, $large_scale, $auth);
            if (is_array($servers)) {
                foreach ($servers as $v) {
                    $this->_drive->add_server($v['host'], $v['port'], $v['weight']);
                }
            }
        } else {
            trigger_error("ERROR! Repeat Seting:Cache->config_memcached().", E_USER_ERROR);
        }
    }

    /**
     * 设定数据keys
     * @param $keys
     */
    public function key($keys)
    {
        $this->_drive->key($keys);
    }

    /**
     * 设定数据Value
     * @param $vals
     */
    public function val($vals)
    {
        $this->_drive->val($vals);
    }

    /**
     * 读取数据
     * @param $keys
     * @return mixed|null
     */
    public function read()
    {
        return $this->_drive->read();
    }

    /**
     * 写入已设定的数据
     * @return bool
     */
    public function write()
    {
        return $this->_drive->write();
    }

    /**
     * 读取数据中$key的值
     * @param $sub_key
     */
    public function get($sub_key)
    {
        return $this->_drive->get($sub_key);
    }

    /**
     * 设定数据中$sub_key为$sub_val
     * @param $sub_key
     * @param $sub_vals
     */
    public function set($sub_key, $sub_val)
    {
        $this->_drive->set($sub_key, $sub_val);
    }

    /**
     * 删除数据
     * @return bool
     */
    public function delete()
    {
        return $this->_drive->delete();
    }

    /**
     * 简单方式，设定$key对应值$val
     * @param $key
     * @param $val
     */
    public function fast_set($key, $val)
    {
        $this->_drive->key($key);
        $this->_drive->val($val);
        $this->_drive->write();
    }

    /**
     * 简单方式，获取$key对应值$val
     *   $sub_key不等于null时 为$val里的$sub_key的值
     * @param $key
     * @param $val
     */
    public function fast_get($key, $sub_key = null)
    {
        $this->_drive->key($key);
        if ($sub_key) {
            return $this->_drive->get($sub_key);
        }
        return $this->_drive->read();
    }

    /**
     * 简单方式，删除$key对应值$val
     * @param $key
     * @param $val
     */
    public function fast_del($key)
    {
        $this->_drive->key($key);
        $this->_drive->delete();
    }

    /**
     * 取得 File:文件名  Memcache|Redis:缓存KEY
     * @return string
     */
    public function filename()
    {
        return $this->_drive->filename();
    }
} 
