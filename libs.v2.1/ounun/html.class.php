<?php 
namespace ounun;

class _html_cache extends cache
{
    private $_cache_time    = -1;
    private $_cache_time_t  = -1;
    private $_cache_size    = -1;

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
     * 文件大小(临时)
     * @return int 文件大小(临时)
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
                // \debug::header('time',$this->_cache_time_t,$this->_debug,__FUNCTION__,__LINE__);
            }
        }else
        {
            $this->_cache_time_t     = (int)$this->get('filemtime_t');
        }
        return $this->_cache_time_t;
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
        return parent::delete();
    }
}
/*
 * --------------------------------------------------------------------
 * CLASS CACHE HTML
 * --------------------------------------------------------------------
 */

class html
{
    /** Cache最小文件大小           */
    const Cache_Mini_Size       = 2024;
    /** Cache生成过程最长临时过度时间 */
    const Cache_Time_Interval   = 300;

	private $_app                = '';
    private $_tpl                = '';
    private $_expire             = 3600;
    private $_now_time;

    // 下面 高级应用
    /** @var bool  */
    private $_stop			 	= false;
    /** @var bool  */
    private $_trim			 	= false;
    /** @var bool  */
    private $_debug		        = false;
    /** @var bool  */
    private $_gzip	            = true;
    /** @var int  */
    private $_cache_time		= 0;
    /** @var null|_html_cache  */
    private $_cache             = null;
    /** @var array  */
    private $_replace_data      = [];
    /**
     * 创建缓存对像
     * @param string $app
     * @param string $cache_root_dir
     * @param int 	 $expire
     * @param string $key
     */
    public function __construct(string $app,string $tpl,$cache_config,string $key='',int $expire=3600,bool $trim=true,bool $debug=true)
    {
        // 初始化参数
        $this->_app          = $app;
        $this->_tpl          = $tpl;
        $this->_expire       = $expire;
        $this->_now_time     = time();

        $this->_stop         = false;
        $this->_trim         = $trim;
        $this->_debug        = $debug;
        $this->_cache_time   = 0;
        // 是否支持gzip
        if(stripos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') === false)
        {
            $this->_gzip    = false;
        }else
        {
            $this->_gzip    = true;
        }
        // Cache
        $this->_cache       = new _html_cache($cache_config,$this->_debug);
        $this->_cache->key("{$app}{$tpl}{$key}");
    }
    /**
     * [1/1] 判断->执行缓存->输出
     * @param   booleam $outpt  ( 是否输出 )
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
        // \debug::header('time',  $this->_cache_time,$this->_debug,__FUNCTION__,__LINE__);
        // \debug::header('expire',$this->_expire,    $this->_debug,__FUNCTION__,__LINE__);
        if( $this->_cache_time + $this->_expire > $this->_now_time )
        {
            // \debug::header('xypc',$this->_cache->filename(),$this->_debug,__FUNCTION__,__LINE__);
            return true;
        }
        $cache_time_t       = $this->_cache->cache_time_tmp();
        // \debug::header('time_t',$cache_time_t,$this->_debug,__FUNCTION__,__LINE__);
    	if($cache_time_t + self::Cache_Time_Interval > $this->_now_time)
    	{
            //  \debug::header('xypc_t',$this->_cache->filename().'.t time:'.$cache_time_t,true,__FUNCTION__,__LINE__);
            return true;
    	}
        $this->_cache_time = 0;
    	return false;
    }
    /**
     * [2/3] 执行缓存程序
     * @param   booleam $outpt  ( 是否输出 )
     */
    public function run_execute(bool $output)
    {
        // \debug::header('xypm',$this->_cache->filename(),$this->_debug,__FUNCTION__,__LINE__);
        //
    	$this->_stop = false;
        $this->_cache->cache_set_time_tmp();
    	// 生成
    	ob_start();
    	register_shutdown_function(array($this,'callback'),$output);
    }
    /**
     * [3/3] 输出缓存
     * @param   booleam  $temp  ( 是否读取临时文件. 默认读取正式文件 )
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
     * @param $output 是否有输出
     */
    public function callback(bool $output)
    {
    	if($this->_stop)
        {
            return;
        }
    	// 执行
        $buffer     = ob_get_contents();
        $filesize   = strlen($buffer);
        ob_clean();
        ob_implicit_flush(1);
        // 写文件
        // \debug::header('xypm_size',$filesize,$this->_debug,__FUNCTION__,__LINE__);
        if($filesize > self::Cache_Mini_Size)
        {
            //  \debug::header('xypm_ok',$this->_cache->filename(),$this->_debug,__FUNCTION__,__LINE__);
            if($this->_replace_data)
            {
                $val    = array_values($this->_replace_data);
                $key    = array_keys($this->_replace_data);
                $buffer = str_replace($key,$val,$buffer);
            }
            
            if($this->_trim)
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
            //  \debug::header('xypm_noc','nocache',$this->_debug,__FUNCTION__,__LINE__);
            if($output)
            {
                header('Content-Length: '. $filesize);
                exit($buffer);
            }
        }
    }

    /**
     * @param array $replace
     */
    public function replace(array $replace)
    {
        $this->_replace_data = $replace;
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
        $this->_stop = true;
        if($output)
        {
            $this->run_output();
        }
    }
}
/* End of file */