<?php

namespace ounun\cache;


class config
{
    /** @var array<\ounun\cache\config> */
    static protected $_inst = [];

    /**
     * @param \ounun\pdo $db
     * @param string $tag
     * @param array $config
     * @param string $config_key
     * @return $this
     */
    static public function instance(string $tag = 'tag', \ounun\pdo $db = null, array $config = [], $config_key = 'cache_file')
    {
        if (empty(static::$_inst[$tag])) {
            if (empty($config)) {
                $config = \ounun\config::$global[$config_key];
            }
            static::$_inst[$tag] = new static($tag, $db, $config);
        }
        return static::$_inst[$tag];
    }

    /** @var array */
    protected $_cache_data = [];

    /** @var core */
    protected $_cache;

    /** @var \ounun\pdo */
    protected $_db;

    /** @var int 最后更新时间，大于这个时间数据都过期 */
    protected $_last_time;

    /**
     * config constructor.
     * @param \ounun\pdo $db
     * @param array $cache_config
     * @param string $tag
     */
    public function __construct(string $tag, \ounun\pdo $db, array $cache_config)
    {
        $this->_db = $db;
        $this->_cache = new core();
        $this->_cache->config($cache_config, "config_{$tag}");
    }

    /**
     * @param int $last_time
     */
    public function last_modify_set(int $last_time)
    {
        $this->_last_time = $last_time;
    }

    /**
     * @param $tag_key
     */
    protected function _clean($tag_key)
    {
        $this->_cache_data[$tag_key] = null;
        unset($this->_cache_data[$tag_key]);
        $this->_cache->fast_del($tag_key);
    }

    /**
     * @param $tag_key
     * @param $mysql_method
     * @param null $args
     * @return mixed
     */
    protected function _data($tag_key, $mysql_method, $args = null)
    {

        if (!$this->_cache_data[$tag_key]) {
            $this->_cache->key($tag_key);
            $c = $this->_cache->read();
            //$this->_cd[$tag_key]->mtime = time();
            //debug_header('$last_modify',$last_modify,true);
            //debug_header('$this_mtime',$this->_cd[$tag_key]->mtime,true);
            if ($c == null) {
                //debug_header('$this_mtime2',222,true);
                $this->_cache_data[$tag_key] = $this->$mysql_method($args);
                $this->_cache->key($tag_key);
                $this->_cache->val(['t' => time(), 'v' => $this->_cache_data[$tag_key]]);
                $this->_cache->write();
            } elseif (!is_array($c) || (int)$c['t'] < $this->_last_time) {
                // debug_header('$this_mtime3',3333,true);
                $this->_cache_data[$tag_key] = $this->$mysql_method($args);
                $this->_cache->key($tag_key);
                $this->_cache->val(['t' => time(), 'v' => $this->_cache_data[$tag_key]]);
                $this->_cache->write();
            } else {
                $this->_cache_data[$tag_key] = $c['v'];
            }
        }
        return $this->_cache_data[$tag_key];
    }
}
