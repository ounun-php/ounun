<?php

namespace ounun\cache;

use ounun\template;

class html
{
    /** Cache最小文件大小           */
    const Cache_Mini_Size = 2024;
    /** Cache生成过程最长临时过度时间 */
    const Cache_Time_Interval = 300;

    /** @var bool */
    public $stop = false;

    /** @var int 当前时间 */
    protected $_now_time;
    /** @var int cache时间长度 */
    protected $_expire = 3600;


    // 下面 高级应用
    /** @var bool */
    protected $_gzip = true;
    /** @var int */
    protected $_cache_time = 0;
    /** @var null|html_base */
    protected $_cache = null;
    /** @var bool 是否去空格 换行 */
    protected $_is_trim = false;
    /** @var bool */
    protected $_is_debug = false;

    /**
     * html constructor.
     * 创建缓存对像
     *
     * @param $cache_config
     * @param string $key
     * @param int $expire
     * @param bool $trim
     * @param bool $debug
     */
    public function __construct($cache_config, string $key = '', int $expire = 3600, bool $trim = true, bool $debug = true)
    {
        $this->stop = false;
        // 初始化参数
        $this->_expire = $expire;
        $this->_now_time = time();

        $this->_cache_time = 0;

        $this->_is_trim   = $trim;
        $this->_is_debug = $debug;
        // 是否支持gzip
        if (stripos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') === false) {
            $this->_gzip = false;
        } else {
            $this->_gzip = true;
        }
        // Cache
        $this->_cache = new html_base($cache_config, $this->_is_debug);
        $this->_cache->key($key);
    }

    /**
     * [1/1] 判断->执行缓存->输出
     * @param bool $output ( 是否输出 )
     */
    public function run(bool $output = true)
    {
        // 是否清理本缓存
        if ($_GET && $_GET['clean']) {
            unset($_GET['clean']);
            $this->clean();
        }
        // 执行
        $is_cache = $this->run_cache_check();
        if ($is_cache) {
            if ($output) {
                $this->run_output();
            }
        } else {
            $this->run_execute($output);
        }
    }

    /**
     * [1/3] 判断是否存缓存
     * @return bool
     */
    public function run_cache_check()
    {
        $this->_cache_time = $this->_cache->cache_time();
        \ounun\debug::header('time',  $this->_cache_time,$this->_is_debug,__FUNCTION__,__LINE__);
        \ounun\debug::header('expire',$this->_expire,    $this->_is_debug,__FUNCTION__,__LINE__);
        if ($this->_cache_time + $this->_expire > $this->_now_time) {
            \ounun\debug::header('xypc',$this->_cache->filename(),$this->_is_debug,__FUNCTION__,__LINE__);
            return true;
        }
        $cache_time_t = $this->_cache->cache_time_tmp();
        \ounun\debug::header('time_t',$cache_time_t,$this->_is_debug,__FUNCTION__,__LINE__);
        if ($cache_time_t + self::Cache_Time_Interval > $this->_now_time) {
            \ounun\debug::header('xypc_t',$this->_cache->filename().'.t time:'.$cache_time_t,$this->_is_debug,__FUNCTION__,__LINE__);
            return true;
        }
        $this->_cache_time = 0;
        return false;
    }

    /**
     * [2/3] 执行缓存程序
     * @param  bool $outpt ( 是否输出 )
     */
    public function run_execute(bool $output)
    {
        \ounun\debug::header('xypm',$this->_cache->filename(),$this->_is_debug,__FUNCTION__,__LINE__);
        $this->stop = false;
        $this->_cache->cache_time_tmp_set();
        // 生成
        ob_start();
        register_shutdown_function(array($this, 'callback'), $output);
    }

    /**
     * [3/3] 输出缓存
     */
    public function run_output()
    {
        if ($this->_cache_time) {
            // 处理 etag
            $etag = $this->_cache_time;
            $etag_http = isset($_SERVER['HTTP_IF_NONE_MATCH']) ? $_SERVER['HTTP_IF_NONE_MATCH'] : '';

            // 处理 cache expire
            header('Expires: ' . gmdate('D, d M Y H:i:s', $this->_now_time + $this->_expire) . ' GMT');
            header('Cache-Control: max-age=' . $this->_expire);
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $this->_cache_time) . ' GMT');

            if ($etag && $etag == $etag_http) {
                header('Etag: ' . $etag, true, 304);
                exit;
            }
            header('Etag: ' . $etag);
            // 输出
            $this->_cache->cache_out($this->_gzip);
        }
        header('HTTP/1.1 404 Not Found');
        exit;
    }

    /**
     * 创建缓存
     * @param bool $output 是否有输出
     */
    public function callback(bool $output)
    {
        if ($this->stop) {
            return;
        }
        // 执行
        $buffer = ob_get_contents();
        $filesize = strlen($buffer);
        ob_clean();
        ob_implicit_flush(1);
        // 写文件
        \ounun\debug::header('xypm_size',$filesize,$this->_is_debug,__FUNCTION__,__LINE__);
        if ($filesize > self::Cache_Mini_Size) {
            \ounun\debug::header('xypm_ok',$this->_cache->filename(),$this->_is_debug,__FUNCTION__,__LINE__);

            $buffer = template::trim($buffer,$this->_is_trim);
            $buffer = gzencode($buffer, 9);
            $this->_cache->cache_html($buffer);
            $this->_cache_time = $this->_cache->cache_time();
            if ($output) {
                $this->run_output();
            }
        } else {
            $this->_cache->delete();
            \ounun\debug::header('xypm_noc','nocache',$this->_is_debug,__FUNCTION__,__LINE__);
            if ($output) {
                header('Content-Length: ' . $filesize);
                exit($buffer);
            }
        }
    }

    /**
     * 是否清理本缓存
     */
    public function clean()
    {
        $this->_cache->delete();
    }

    /**
     * 看是否存在cache
     * @return int 小于0:无Cache 大于0:创建Cache时间
     */
    public function cache_time(): int
    {
        return $this->_cache_time;
    }

    /**
     * 停止Cache
     * @param $output
     */
    public function stop(bool $output)
    {
        $this->stop = true;
        if ($output) {
            \v::$tpl->replace();
            $this->run_output();
        }
    }
}
