<?php 
namespace ounun;

/*
 * --------------------------------------------------------------------
 * CLASS CACHE HTML
 * --------------------------------------------------------------------
 */

class CacheHtml
{   
	private $dir_sub            = '';
    private $dir_root           = '';
    private $expire             = 0;
    private $time_now;
    
    private $server_software    = 'apache';

    private $file_path;
    private $file_path_temp;
    
    // 下面 高级应用
    private $_stop			 	= false;
    private $_cache_time		= 0;
    private $_debug		        = false;
    /**
     * 创建缓存对像
     * @param string $app_name
     * @param string $cache_root_dir
     * @param int 	  $expire
     * @param string $key
     */
    public function __construct($app_name,$cache_root_dir,$key='',$expire=1800)
    {
        //clearstatcache();
        // 初始化参数        
        $this->dir_sub          = $app_name;
        $this->dir_root         = $cache_root_dir;
        $this->expire           = $expire;
        $this->time_now         = time();
        // 获得当前运行环境
        if(stripos($_SERVER['SERVER_SOFTWARE'], 'nginx') !== false)
        {
            $this->server_software = 'nginx';
        }elseif(stripos($_SERVER['SERVER_SOFTWARE'], 'apache') !== false)
        {
            $this->server_software = 'apache';
        }
        // 
        $this->key_set($key);
        // 是否清理本缓存
        if($_GET && $_GET['clean'])
        {
			unset($_GET['clean']);
            $this->_clean();
        }
    }
    /**
     * [1/1] 判断->执行缓存->输出
     * @param   booleam $outpt  ( 是否输出 )
     */
    public function action($output = true)
    {
    	$file_path  = $this->is_cache();
    	if($file_path)
    	{
    		if($output)
    		{
    			$this->output($file_path);
    		}
    	}else 
    	{
    		$this->run($output);
    	}
    	return;
    }
    /**
     * 看是否存在cachefile
     */
    public function is_cachefile()
    {
    	return $this->_cache_time;
    }


    /**
     * [1/3] 判断是否存缓存
     */
    public function is_cache()
    {
    	//  header("xyp_expire: " . $this->expire);
    	//  $filemtime = filemtime($this->file_path)+$this->expire;
    	//  header("time_b: " . $filemtime);
    	//  header("time_e: " . $this->time_now);
    	$this->_cache_time = 0;
    	if(file_exists($this->file_path) )
    	{
    		$this->_cache_time = filemtime($this->file_path);
    		if( $this->_cache_time + $this->expire > $this->time_now )
    		{
                if($this->_debug)
                {
                    header("xypc: " . $this->file_path);
                }
    			return $this->file_path;
    		}
    	}
    	
    	if(file_exists($this->file_path_temp) &&
    			//filesize($this->file_path_temp) > 10 &&
    			filemtime($this->file_path_temp) + $this->expire + 60 > $this->time_now)
    	{
            if($this->_debug)
            {
    		    header("xypt: " . $this->file_path_temp);
            }
    		return $this->file_path_temp;
    	}
    	return false;
    }
    /**
     * [2/3] 执行缓存程序
     * @param   booleam $outpt  ( 是否输出 )
     */
    public function run($output)
    {
        if($this->_debug)
        {
            header("xypm: " . $this->file_path);
        }
        //
    	$this->_stop = false;
        $this->_dir(dirname($this->file_path));
    	if(file_exists($this->file_path_temp))
        {
            unlink($this->file_path_temp);
        }
    	// 生成  先设定temp
    	if(file_exists($this->file_path))
    	{
    		rename($this->file_path, $this->file_path_temp);
    		//touch($this->file_path_temp);
    	}
    	// 生成
    	ob_start();
    	register_shutdown_function(array($this,'callback'),$output);
    }

    public function run_stop($output)
    {
        $this->_stop = true;
        if($output && file_exists($this->file_path_temp) )
        {
            if($this->_debug)
            {
                header("xypstop: " . $this->_cache_time);
            }
            //
            rename($this->file_path_temp, $this->file_path);
            touch($this->file_path);
            // ---
            $this->output($this->file_path);
        }
    }
    /**
     * [3/3] 输出缓存
     * @param   booleam  $temp  ( 是否读取临时文件. 默认读取正式文件 )
     */
    public function output($file_path)
    {
    	//	echo $file_path;
    	//  header('Content-Encoding: gzip');
    	//  header('Vary: Accept-Encoding' );
    	//  header('Content-Length: '. filesize($file_path) );
    	//  readfile($file_path);

    	//  exit();
    	if(file_exists($file_path))
    	{
    		// 处理 etag
    		$etag       = filemtime($file_path);
    		$etag_http  = isset($_SERVER['HTTP_IF_NONE_MATCH'])?$_SERVER['HTTP_IF_NONE_MATCH']:'';
    		if($etag && $etag == $etag_http)
    		{
    			header('Etag: ' . $etag, true, 304);
    			exit;
    		}
    		
    		// 处理 cache expire
    		header('Expires: '. gmdate('D, d M Y H:i:s', $this->time_now + $this->expire). ' GMT');
    		header('Cache-Control: max-age='. $this->expire);
    		header('Etag: '. $etag);

    		// 输出 ( 不支持 gzip )
    		if(stripos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') === false)
    		{
    			$content    = file_get_contents($file_path);
    			$content    = gzdecode($content);
    			$filesize   = strlen($content);
    			header('Content-Length: '. $filesize);
    			exit($content);
    		}
    		
    		// 输出 ( 支持 gzip )
    		header('Content-Encoding: gzip');
    		// header('Vary: Accept-Encoding');
    		header('Content-Length: '. filesize($file_path));
    		readfile($file_path);
    		exit;
    	}
    	header('HTTP/1.1 404 Not Found');
    	exit;
    }



    /**
     * 设定$key唯一标识
     * @param string $key
     */
    public function key_set($key)
    {
    	// 得到URL Key
    	$key  = $key?$key:$this->_key();
    	// 通过获得文件名
    	$md5  = md5($key);
    	$dir  = $this->dir_root. $this->dir_sub. '/'. $md5{0}. '/'. $md5{1}. '/';
    	 
    	$this->file_path      = $dir. $md5. '.z';
    	$this->file_path_temp = $dir. $md5. '.z.t';
    }
    /*
     * 创建缓存
     * @param   booleam $status ( 状态 )
     * @param   booleam $outpt  ( 是否输出 )
     */
    public function callback($output)
    {
    	if($this->_stop) return;
    	
        $buffer     = ob_get_contents();
        $filesize   = strlen($buffer);
        ob_clean();
        ob_implicit_flush(1);
        // 写文件
        header("xypm_size: " . $filesize);
        if($filesize > 2048)
        {
            header("xypm_ok: " . $this->file_path);
            $buffer     = preg_replace(array('/<!--.*?-->/','/[^:C\-l]\/\/.*?\n/', '/\/\*.*?\*\//', '/[\n\r\t]*?/', '/\s{2,}/'), 
									   array('','', '', '', ' '), $buffer);
            $buffer     = gzencode($buffer, 9);
            file_put_contents($this->file_path, $buffer);
            if(file_exists($this->file_path_temp))
            {
                unlink($this->file_path_temp);
            }            
            if($output)
            {
                $this->output($this->file_path);
            }
        }elseif(file_exists($this->file_path_temp) && 
                $filesize < 2048 && 
                filesize($this->file_path_temp) > 10)
        {
            if($this->_debug)
            {
                header("xypm_bad: " . $this->file_path_temp);
            }
            if($output)
            {
                $this->output($this->file_path_temp);
            }
        }else
        {
            if($this->_debug)
            {
                header("xypm_noc: nocache");
            }
            if($output)
            {
                header('Content-Length: '. $filesize);
                exit($buffer);
            }
        }
    }
    /**
     * 清理缓存 
     */
    private function _clean()
    {
        if(file_exists($this->file_path))
        {
            unlink($this->file_path);
        }
        if(file_exists($this->file_path_temp))
        {
            unlink($this->file_path_temp);
        }        
    }
    /**
     * 得到缓存Key 
     */
    private function _key()
    {
  		return $_SERVER['REQUEST_URI'];
//         $t = explode('?', $_SERVER['REQUEST_URI'], 2);
// 		return $_SERVER['HTTP_HOST'].$t[0];
    }
    
    
    /*
     * 设置目录
     */
    private function _dir($dir, $mode = 0777)
    {
        if(!is_dir($dir))
        {
            mkdir($dir, $mode, true);
        }
		return is_dir($dir);
    }
}


/* End of file */