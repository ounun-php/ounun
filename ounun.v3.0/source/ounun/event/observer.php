<?php

namespace ounun\event;

class observer implements \SplObserver
{
    /** @var array 配制 */
    protected $config = [];

    /** @var string 当前目录 */
    protected $dir = '';

    /** @var array 插件数据 */
    static protected $plugin = [];

    public function __construct($dir)
    {
        $this->dir = $dir;
        if (is_file($dir . 'config.php')) {
            $config = require($dir . 'config.php');
            foreach ($config as $file => $events) {
                foreach ($events as $event) {
                    $this->config[$event][] = $file;
                }
            }
        }
    }

    public function update(\SplSubject $subject)
    {
        $event = $subject->event;
        if (empty($this->config[$event])) {
            return false;
        }

        foreach ($this->config[$event] as $file) {
            if (!isset(static::$plugin[$file])) {
                require_once($this->dir . $file . '.php');
                $class = 'plugin_' . $file;
                static::$plugin[$file] = new $class($subject);
            }
            static::$plugin[$file]->$event();
        }
    }
}
