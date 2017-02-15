<?php 
/** 命名空间 */
namespace plugins;

use ounun\Http;

class erlang
{
    private $_key;
    private $_host;
    private $_port;

    /**
     * erlang constructor.
     * @param string $key
     * @param string $host
     * @param int $port
     */
    public function __construct(string $key,string $host,int $port)
    {
        $this->set_config($key,$host,$port);
    }

    /**
     * @param string $key
     * @param string $host
     * @param int $port
     */
    public function set_config(string $key,string $host,int $port)
    {
        $this->_key  = $key;
        $this->_host = $host;
        $this->_port = $port;
    }
    /**
     * 得到服务器当前在线
     * @param uint $sid
     */
    public function online($sid)
    {
        $data	= "[]";
        return $this->_erlang_call('gm_api','online',$sid,$data);
    }

    /**
     * 编译 服务器data数据
     * @param uint $sid
     */
    public function cmd_master_make($sid)
    {
        $data    = "[]";
        return $this->_erlang_call('gc_master','cmd_make',$sid,$data);
    }
    /**
     * 统一调用调用
     * @param uint   $sid
     * @param string $fun
     * @param string $arg_data
     * @return Ambigous <multitype:, multitype:boolean string >
     */
    protected function _erlang_call($mod,$fun,$sid,$arg_data)
    {
        $time = time();
        $md5  = md5($sid.'_'.$arg_data.'_'.$time.'_'.$this->_key);
        return \ounun\Http::Erlang($mod,$fun,"{ {$sid},\"{$md5}\",{$time},{$arg_data}}",$this->_host,$this->_port);
    }

    /**
     * @param $fun  : 方法
     * @param $data : 数据
     */
    protected function _port($mod,$fun,$data="[]",$host="127.0.0.1",$port=18443)
    {
        $host 	= "http://{$host}:{$port}/";
        //echo $host;
        $model 	= "{{$mod},{$fun},{$data}}";
        return Http::post($host,$model, [], 600);
    }

    /**
     * 得到give数据元组
     * @param uint $goods_id 	物品ID
     * @param uint $count    	数量
     * @param uint $streng	  	强化等级
     * @param uint $name_color  物品名称的颜色
     * @param uint $bind		是否绑定(0:不绑定 1:绑定)
     * @param uint $expiry_type 有效期类型，0:不失效，1：秒，  2：天，请多预留几个以后会增加
     * @param uint $expiry		有效期，到期后自动消失，并发系统邮件通知
     */
    protected function _give_good($goods_id,$count,$streng,$name_color,$bind,$expiry_type,$expiry)
    {
        // {give,2005,1,0,1,1,0,0}
        return "{give,{$goods_id},{$count},{$streng},{$name_color},{$bind},{$expiry_type},{$expiry}}";
    }

    /**
     *
     */
    protected function _string_binary($string)
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

