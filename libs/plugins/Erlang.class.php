<?php 
/** 命名空间 */
namespace plugins;

use ounun\Http;

class Erlang
{
    protected $_key;
    protected $_sid; // hub_id
    protected $_host;
    protected $_port;

    /**
     * erlang constructor.
     * @param string $key
     * @param string $host
     * @param int $port
     */
    public function __construct(string $key,int $sid_hub_id,string $host,int $port)
    {
        $this->set_config($sid_hub_id,$host,$port,$key);
    }

    /**
     * @param string $key
     * @param string $host
     * @param int $port
     */
    public function set_config(int $sid_hub_id=0,string $host='',int $port=0,string $key='')
    {
        if($key)
        {
            $this->_key  = $key;
        }
        if($sid_hub_id)
        {
            $this->_sid = $sid_hub_id;
        }
        if($host)
        {
            $this->_host = $host;
        }
        if($port)
        {
            $this->_port = $port;
        }
    }
    /**
     * 统一调用调用
     * @param uint   $sid
     * @param string $fun
     * @param string $arg_data
     * @return Ambigous <multitype:, multitype:boolean string >
     */
    protected function _erlang_call($mod,$fun,$arg_data)
    {
        $time = time();
        $md5  = md5($this->_sid.'_'.$arg_data.'_'.$time.'_'.$this->_key);
        return $this->_port($mod,$fun,"{ {$this->_sid},\"{$md5}\",{$time},{$arg_data}}");
    }

    /**
     * @param $fun  : 方法
     * @param $data : 数据
     */
    protected function _port($mod,$fun,$data="[]")
    {
        $host 	= "http://{$this->_host}:{$this->_port}/";
        $model 	= "{{$mod},{$fun},{$data}}";
        return Http::post($host,$model, [], 600);
    }

    /**
     * 得到give数据元组
     * @param uint $goods_id 	物品ID
     * @param uint $count    	数量
     * @param uint $args        扩展参数
     */
    protected function give($goods_id,$count,$exts)
    {
        // {give,2005,1,[0,1,1,0,0]}
        return "{g,{$goods_id},{$count},{$exts}}";
    }
    /**
     * 例子:
     *   $dir	 = "<<".private_string_binary($dir).">>";
     *   $cmd	 = "<<".private_string_binary($cmd).">>";
     *   $data   = "[{$dir},{$cmd}]";
     */
    protected function string_binary($string)
    {
        $i      = 0;
        $number = [];
        while (isset($string{$i}))
        {
            $number[] = ord($string{$i});
            $i++;
        }
        return implode(',', $number);
    }
}

