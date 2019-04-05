<?php

namespace ounun\cache\drive;


class file extends base
{
    /** @var string 存放路径 */
    private $_root = '';

    /** @var bool false:混合数据 true:字符串 */
    private $_format_string = false;

    /** @var bool false:少量    true:大量 */
    private $_large_scale = false;

    /** @var string cache文件名称 */
    private $_filename = null;

    /** @var array  数据 */
    private $_data = null;

    /** @var bool false:没读    true:已读 */
    private $_is_read = false;

    /**
     * 设定 file Cache配制
     * @param string $mod
     * @param $root
     * @param bool|false $large_scale
     */
    public function __construct($mod = 'def', $root = '', $format_string = false, $large_scale = false)
    {
        $this->_mod = $mod;
        $this->_root = $root;
        $this->_large_scale = $large_scale;
        $this->_format_string = $format_string;
    }

    /**
     * 设定数据keys
     * @param $key
     */
    public function key($key)
    {
        if ($this->_large_scale) {
            $key = md5($key);
            $key = "{$key[0]}{$key[1]}/{$key[2]}{$key[3]}/" . substr($key, 4);
        }
        if ($this->_format_string) {
            $this->_filename = "{$this->_root}{$this->_mod}/{$key}.z";
            $this->_data = '';
            $this->_is_read = false;
        } else {
            $this->_filename = "{$this->_root}{$this->_mod}/{$key}.php";
            $this->_data = null;
            $this->_is_read = false;
        }
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
        if (file_exists($this->_filename)) {
            if ($this->_format_string) {
                $this->_data = file_get_contents($this->_filename);
            } else {
                $this->_data = include $this->_filename;
            }
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
        $filedir = dirname($this->_filename);
        if (!is_dir($filedir)) {
            mkdir($filedir, 0777, true);
        }
        if (file_exists($this->_filename)) {
            unlink($this->_filename);
        }
        if ($this->_format_string) {
            return file_put_contents($this->_filename, $this->_data);
        } else {
            $str = var_export($this->_data, true);
            return file_put_contents($this->_filename, '<?php ' . "return {$str};" . '?>');
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
        if (file_exists($this->_filename)) {
            return unlink($this->_filename);
        }
        return true;
    }

    /**
     * 取得 File:文件名  Memcache|Redis:缓存KEY
     * @return string
     */
    public function filename()
    {
        return $this->_filename;
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
