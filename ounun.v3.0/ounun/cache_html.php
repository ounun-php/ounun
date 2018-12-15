<?php 
namespace ounun;

class cache_html
{
    /** Cache最小文件大小           */
    const Cache_Mini_Size       = 2024;
    /** Cache生成过程最长临时过度时间 */
    const Cache_Time_Interval   = 300;
    /** @var bool  */
    public $stop			    = false;


    /** @var int 当前时间 */
    protected $_now_time;

    /** @var int cache时间长度 */
    protected $_expire          = 3600;


    // 下面 高级应用
    /** @var bool  */
    protected $_gzip	        = true;

    /** @var int  */
    protected $_cache_time		= 0;

    /** @var null|_cache_html  */
    protected $_cache           = null;

    /** @var bool 是否去空格 换行 */
    protected $_is_trim			= false;

    /** @var bool  */
    protected $_is_debug		= false;


    /**
     * 创建缓存对像 cache_html constructor.
     * @param $cache_config
     * @param string $key
     * @param int $expire
     * @param bool $trim
     * @param bool $debug
     */
    public function __construct($cache_config,string $key='',int $expire=3600,bool $trim=true,bool $debug=true)
    {
        $this->stop          = false;
        // 初始化参数
        $this->_expire       = $expire;
        $this->_now_time     = time();

        $this->_cache_time   = 0;

        $this->_is_trim      = $trim;
        $this->_is_debug     = $debug;
        // 是否支持gzip
        if(stripos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') === false)
        {
            $this->_gzip    = false;
        }else
        {
            $this->_gzip    = true;
        }
        // Cache
        $this->_cache       = new _cache_html($cache_config,$this->_is_debug);
        $this->_cache->key($key);
    }

    /**
     * [1/1] 判断->执行缓存->输出
     * @param bool $outpt  ( 是否输出 )
     */
    public function run(bool $output = true)
    {
        // 是否清理本缓存
        if($_GET && $_GET['clean'])
        {
            unset($_GET['clean']);
            $this->clean();
        }
        // 执行
        $is_cache  = $this->run_cache_check();
        if($is_cache)
        {
            if($output)
            {
                $this->run_output();
            }
        }else
        {
            $this->run_execute($output);
        }
    }

    /**
     * [1/3] 判断是否存缓存
     * @return bool
     */
    public function run_cache_check()
    {
        $this->_cache_time  = $this->_cache->cache_time();
        //exit("\$this->_cache_time:{$this->_cache_time }");
        // \debug::header('time',  $this->_cache_time,$this->_is_debug,__FUNCTION__,__LINE__);
        // \debug::header('expire',$this->_expire,    $this->_is_debug,__FUNCTION__,__LINE__);
        if( $this->_cache_time + $this->_expire > $this->_now_time )
        {
            // \debug::header('xypc',$this->_cache->filename(),$this->_is_debug,__FUNCTION__,__LINE__);
            return true;
        }
        $cache_time_t       = $this->_cache->cache_time_tmp();
        // \debug::header('time_t',$cache_time_t,$this->_is_debug,__FUNCTION__,__LINE__);
        if($cache_time_t + self::Cache_Time_Interval > $this->_now_time)
        {
            // \debug::header('xypc_t',$this->_cache->filename().'.t time:'.$cache_time_t,true,__FUNCTION__,__LINE__);
            return true;
        }
        $this->_cache_time = 0;
        return false;
    }

    /**
     * [2/3] 执行缓存程序
     * @param  bool $outpt  ( 是否输出 )
     */
    public function run_execute(bool $output)
    {
        // \debug::header('xypm',$this->_cache->filename(),$this->_is_debug,__FUNCTION__,__LINE__);
        $this->stop = false;
        $this->_cache->cache_set_time_tmp();
        // 生成
        ob_start();
        register_shutdown_function(array($this,'callback'),$output);
    }

    /**
     * [3/3] 输出缓存
     * @param bool  $temp  ( 是否读取临时文件. 默认读取正式文件 )
     */
    public function run_output()
    {
        if($this->_cache_time)
        {
            // 处理 etag
            $etag       = $this->_cache_time;
            $etag_http  = isset($_SERVER['HTTP_IF_NONE_MATCH'])?$_SERVER['HTTP_IF_NONE_MATCH']:'';

            // 处理 cache expire
            header('Expires: '. gmdate('D, d M Y H:i:s', $this->_now_time + $this->_expire). ' GMT');
            header('Cache-Control: max-age='. $this->_expire);
            header('Last-Modified: '.gmdate('D, d M Y H:i:s', $this->_cache_time).' GMT');

            if($etag && $etag == $etag_http)
            {
                header('Etag: ' . $etag, true, 304);
                exit;
            }
            header('Etag: '.    $etag);
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
        if($this->stop)
        {
            return;
        }
        // 执行
        $buffer     = ob_get_contents();
        $filesize   = strlen($buffer);
        ob_clean();
        ob_implicit_flush(1);
        // 写文件
        // \debug::header('xypm_size',$filesize,$this->_is_debug,__FUNCTION__,__LINE__);
        if($filesize > self::Cache_Mini_Size)
        {
            // \debug::header('xypm_ok',$this->_cache->filename(),$this->_is_debug,__FUNCTION__,__LINE__);
            if(\v::$stpl_rd)
            {
                $buffer = strtr($buffer,\v::$stpl_rd);
            }

            if($this->_is_trim)
            {
                $buffer = preg_replace(['/<!--.*?-->/','/[^:\-\"]\/\/[^\S].*?\n/', '/\/\*.*?\*\//', '/[\n\r\t]*?/', '/\s{2,}/','/>\s?</','/<!--.*?-->/','/\"\s?>/'],
                                       [''            ,''                        , ''             , ''            , ' '       ,'><'     ,''            ,'">'],
                                       $buffer);
            }
            $buffer     = gzencode($buffer, 9);
            $this->_cache->cache_html($buffer);
            $this->_cache_time = $this->_cache->cache_time();
            if($output)
            {
                $this->run_output();
            }
        }else
        {
            $this->_cache->delete();
            // \debug::header('xypm_noc','nocache',$this->_is_debug,__FUNCTION__,__LINE__);
            if($output)
            {
                header('Content-Length: '. $filesize);
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
    public function cache_time():int
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
        if($output)
        {
            $this->run_output();
        }
    }
}


class _cache_html extends cache
{
    private $_cache_time    = -1;
    private $_cache_time_t  = -1;
    private $_cache_size    = -1;
    private $_cache_size_t  = -1;

    private $_debug         = false;

    /**
     * 构建函数
     * @param $cfg
     */
    public function __construct($cfg,$debug=false)
    {
        parent::__construct();
        $type_list           = array(self::Type_File,self::Type_Memcache,self::Type_Redis);
        $type                = in_array($cfg['type'],$type_list)?$cfg['type']:self::Type_File;
        if(self::Type_Redis == $type)
        {
            $cfg['type']            = $type;
            $cfg['format_string']   = false;
            $cfg['large_scale']     = true;
        }elseif(self::Type_Memcache == $type)
        {
            $cfg['type']            = $type;
            $cfg['format_string']   = false;
            $cfg['large_scale']     = true;
        }else//if(self::Type_File == $type)
        {
            $cfg['type']            = $type;
            $cfg['format_string']   = true;
            $cfg['large_scale']     = true;
        }
        $this->_debug = $debug;
        $this->config($cfg);
    }

    /**
     * 修改时间
     * @return int 修改时间
     */
    public function cache_time()
    {
        if( 0 <= $this->_cache_time )
        {
            return $this->_cache_time;
        }
        //
        $this->_cache_time         = 0;
        if(self::Type_File == $this->_type)
        {
            $filename = $this->filename();
            // \debug::header('filename',$filename,$this->_debug,__FUNCTION__,__LINE__);
            if(file_exists($filename) )
            {
                $this->_cache_time = filemtime($filename);
                // \debug::header('cache_time',$this->_cache_time,$this->_debug,__FUNCTION__,__LINE__);
            }
        }else
        {
            $this->_cache_time     = (int)$this->get('filemtime');
        }
        return $this->_cache_time;
    }
    /**
     * 文件生成时间(临时)
     * @return int 文件生成时间(临时)
     */
    public function cache_time_tmp()
    {
        if( 0 <= $this->_cache_time_t )
        {
            return $this->_cache_time_t;
        }
        //
        $this->_cache_time_t        = 0;
        if(self::Type_File == $this->_type)
        {
            $filename = $this->filename().'.t';
            // \debug::header('file',$filename,$this->_debug,__FUNCTION__,__LINE__);
            if(file_exists($filename) )
            {
                $this->_cache_time_t = filemtime($filename);
                $this->_cache_size_t = filesize($filename);
                // \debug::header('time',$this->_cache_time_t,$this->_debug,__FUNCTION__,__LINE__);
            }
        }else
        {
            $this->_cache_time_t     = (int)$this->get('filemtime_t');
        }
        return $this->_cache_time_t;
    }

    /**
     * 文件大小(临时)
     * @return int
     */
    public function cache_size_tmp()
    {
        return $this->_cache_size_t;
    }
    /**
     * 标记(临时)
     */
    public function cache_set_time_tmp()
    {
        $this->_cache_time_t = time();
        if(self::Type_File == $this->_type)
        {
            $filename = $this->filename().'.t';
            // \debug::header('file',$filename,$this->_debug,__FUNCTION__,__LINE__);
            if(file_exists($filename) )
            {
                touch($filename);
            }else
            {
                $filedir    = dirname($filename);
                if(!is_dir($filedir))
                {
                    mkdir($filedir, 0777, true);
                }
                touch($filename);
            }
        }else
        {
            $this->set('filemtime_t',$this->_cache_time_t);
            $this->write();
        }
    }
    /**
     * 文件大小
     * @return int 文件大小
     */
    public function cache_size()
    {
        if( 0 <= $this->_cache_size )
        {
            return $this->_cache_size;
        }
        if(self::Type_File == $this->_type)
        {
            $filename = $this->filename();
            // \debug::header('file',$filename,$this->_debug,__FUNCTION__,__LINE__);
            if(file_exists($filename) )
            {
                $this->_cache_size = filesize($filename);
                // \debug::header('size',$this->_cache_size,$this->_debug,__FUNCTION__,__LINE__);
            }
            $this->_cache_size     = 0;
        }else
        {
            $this->_cache_size     = (int)$this->get('filesize');
        }
        return $this->_cache_size;
    }
    /**
     * 保存数据
     */
    public function cache_html($html)
    {
        $this->_cache_time  = time();
        if(self::Type_File == $this->_type)
        {
            $this->val($html);
            $this->write();
            $filename = $this->filename().'.t';
            // \debug::header('delfile',$filename,$this->_debug,__FUNCTION__,__LINE__);
            if(file_exists($filename) )
            {
                unlink($filename);
            }
        }else
        {
            $this->val(array('filemtime'=>$this->_cache_time,'filesize'=>strlen($html),'html'=>$html));
            $this->write();
        }
    }
    /**
     * 保存数据
     */
    public function cache_out($gzip)
    {
        // 输出
        if($gzip)
        {// 输出 ( 支持 gzip )
            header('Content-Encoding: gzip');
            if(self::Type_File == $this->_type)
            {
                $filename = $this->filename();
                header('Content-Length: '. filesize($filename));
                readfile($filename);
                exit;
            }else
            {
                header('Content-Length: '. $this->get('filesize'));
                exit($this->get('html'));
            }
        }else
        {// 输出 ( 不支持 gzip )
            if(self::Type_File == $this->_type)
            {
                $content    = $this->read();
            }else
            {
                $content    = $this->get('html');
            }
            $content    = gzdecode($content);
            $filesize   = strlen($content);
            header('Content-Length: '. $filesize);
            exit($content);
        }
    }
    /**
     * 删除数据
     * @return bool       */
    public function delete()
    {
        $this->_cache_time    = -1;
        $this->_cache_time_t  = -1;
        $this->_cache_size    = -1;
        $this->_cache_size_t  = -1;

        $filename = $this->filename().'.t';

        if (file_exists($filename))
        {
            return unlink($filename);
        }
        return parent::delete();
    }
}
/*
 * --------------------------------------------------------------------
 * CLASS CACHE HTML
 * --------------------------------------------------------------------
 */