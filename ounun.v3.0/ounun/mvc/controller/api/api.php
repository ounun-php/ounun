<?php
namespace ounun\mvc\controller\api;

class api extends \v
{
    /**
     * 后台数据接口
     * @param $mod
     */
    public function interface_mysql($mod)
    {
        $this->init_page("/api/interface_mysql.html",false,false);

        $secure                 = new \ounun\mvc\model\admin\secure(Const_Key_Conn_Private);
        list($check,$error_msg) = $secure->check($_GET,time());
        if($check)
        {
            $db   = \ounun\config::$database[\ounun\config::$app_name];
            $data = $secure->encode($db);
            $rs   = ['ret'=>$check,'data'=>$data];
        }else
        {
            $rs   = ['ret'=>$check,'error'=>$error_msg];
        }
        exit(json_encode($rs,JSON_UNESCAPED_UNICODE));
    }
}