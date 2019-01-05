<?php
namespace ounun\event;

class observer implements \SplObserver
{
    /** @var array 配制 */
    protected $config = [];

    /** @var string 当前目录 */
    protected $dir    = '';

    /** @var array 插件数据 */
	static protected $plugin = [];
	
	public function __construct($dir)
	{
		$this->dir = $dir;
		if(file_exists($dir.'config.php')){
			$config = include($dir.'config.php');
			foreach ($config as $file=>$events){
				foreach ($events as $event){
					$this->config[$event][] = $file;
				}
			}
		}
	}
	
	public function update(\SplSubject $subject)
	{
		$event = $subject->event;
		if(empty($this->config[$event])){
			return false;
		}

		foreach ($this->config[$event] as $file){
			if (!isset(self::$plugin[$file])){
				require_once($this->dir.$file.'.php');
				$class = 'plugin_'.$file;
				self::$plugin[$file] = new $class($subject);
			}
			self::$plugin[$file]->$event();
		}
	}	
}