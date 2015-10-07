<?php 
namespace ounun;

class Debug 
{	
	/** 日志数组 */
	private $_logs 	   = array();
	/** 输出文件名 */
	private $_filename;
	/** 构造函数 */
	public function __construct($filename = 'ounun_debug.txt')
	{
		ob_start();
		register_shutdown_function(array($this,'callback'));
		
		$this->_filename	= $filename;
	}
	/**
	 * 调试日志
	 */
	public function logs($k,$log)
	{
		if($k && $log)
		{
			$this->_logs[$k] = $log;
		}
	}
	
	/**
	 * 内部内调
	 */
	public function callback()
	{
		$buffer     = ob_get_contents();
		ob_clean();
		ob_implicit_flush(1);
		$this->logs('buffer', $buffer);
        $this->write();
		exit($buffer);
	}
	
	/**
	 * 析构调试相关
	 */
	// public function __destruct()
    public function write()
	{
		if(!$this->_logs) 
		{
			return ;
		}
		/**  */
		$filename = $this->_filename;
		$logs     = array(
							'DATE'=> date("Y-m-d H:i:s"),
							'URL' => $_SERVER['REQUEST_URI'],
						 );
		if($_GET)
		{
			$logs['GET']        = $_GET;
		}
		if($_POST)
		{
			$logs['POST']       = $_POST;
		}
		if($this->_logs)
		{
			$logs['LOGS'] 		= $this->_logs;
		}
		/**  */
		$var      				= var_export($logs,true);
		if (file_exists($filename))
		{
			$var  = $var . "\n------------------\n" .file_get_contents($filename);
		}
		file_put_contents($filename, $var);
		//*/
	}
}

?>