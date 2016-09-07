<?php
namespace ounun;

class Debug
{
	/** 日志数组 */
	private $_logs 	        = array();

	/** 输出文件名 */
	private $_filename      = '';

    /** 是否输出 buffer */
    private $_is_out_buffer = true;

    /** 是否输出 get */
    private $_is_out_get    = true;

    /** 是否输出 post */
    private $_is_out_post   = true;

    /** 是否输出 url */
    private $_is_out_url    = true;

    /**
     * 构造函数
     * Debug constructor.
     * @param string $filename      输出文件名
     * @param bool $is_out_buffer   是否输出 buffer
     * @param bool $is_out_get      是否输出 get
     * @param bool $is_out_post     是否输出 post
     * @param bool $is_out_url      是否输出 url
     */
	public function __construct($filename = 'ounun_debug.txt',
                                $is_out_buffer=true,$is_out_get=true,$is_out_post=true,$is_out_url=true)
	{
		ob_start();
		register_shutdown_function(array($this,'callback'));

		$this->_filename	    = $filename;
        $this->_is_out_buffer   = $is_out_buffer;
        $this->_is_out_get      = $is_out_get;
        $this->_is_out_post     = $is_out_post;
        $this->_is_out_url      = $is_out_url;
	}

    /**
     * 调试日志
     * @param $k
     * @param $log          日志内容
     * @param $is_replace   是否替换
     */
	public function logs($k,$log,$is_replace = true)
	{
		if($k && $log)
		{
            // 直接替换
			if($is_replace)
            {
                $this->_logs[$k] = $log;
            }else
            {
                if($this->_logs[$k])
                {
                    // 已是数组,添加到后面
                    if(is_array($this->_logs[$k]))
                    {
                        $this->_logs[$k][] = $log;
                    }else
                    {
                        $this->_logs[$k]   = array($this->_logs[$k],$log);
                    }
                }else
                {
                    $this->_logs[$k] = $log;
                }
            }
		}
	}

	/**
	 * 停止调试
	 */
	public function stop()
	{
		$this->_logs 		= array();
		$this->_filename	= '';
	}

	/**
	 * 内部内调
	 */
	public function callback()
	{
		$buffer     = ob_get_contents();
		ob_clean();
		ob_implicit_flush(1);
		if($this->_is_out_buffer)
        {
            $this->logs('buffer', $buffer);
        }
		$this->write();
		exit($buffer);
	}


	/**
	 * 析构调试相关
	 */
	public function write()
 // public function __destruct()
	{
		if(!$this->_logs  || !$this->_filename)
		{
			return ;
		}
		/**  */
		$filename = Dir_Root.$this->_filename;
		$logs     = array( 'DATE'=> date("Y-m-d H:i:s") );
        if($this->_is_out_url)
        {
            $logs['URI'] = $_SERVER['REQUEST_URI'];
            $logs['URL'] = url_original($_SERVER['REQUEST_URI']);
        }
		if($this->_is_out_get && $_GET)
		{
			$logs['GET']        = $_GET;
		}
		if($this->_is_out_post && $_POST)
		{
			$logs['POST']       = $_POST;
		}
		if($this->_logs)
		{
			$logs['LOGS'] 		= $this->_logs;
		}
		$this->_logs            = array();
		$var      				= var_export($logs,true);
		if (file_exists($filename))
		{
			$var  = $var . "\n------------------\n" .file_get_contents($filename);
		}
		file_put_contents($filename, $var);
	}
}
