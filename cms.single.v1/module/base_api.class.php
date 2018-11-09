<?php
namespace module;

use admin\secure;

class base_api extends \v
{
    /**
     * 广告 PC
     * @param $mod array
     */
    public function pc($mod)
    {
        exit('var $__m_g_com='.json_encode(\ads::pc)."\n");
    }

    /**
     * 广告 Wap
     * @param $mod array
     */
    public function wap($mod)
    {
        exit('var $__m_g_com='.json_encode(\ads::wap)."\n");
    }

    /**
     * 后台数据接口
     * @param $mod
     */
    public function interface_mysql($mod)
    {
        $this->init_page("/api/interface_mysql.html",false,false);

        $secure                 = new secure(Const_Key_Conn_Private);
        list($check,$error_msg) = $secure->check($_GET,time());
        if($check)
        {
            $key  = $_GET['release']?\ounun_scfg::$app:\ounun_scfg::$app."_debug";
            $db   = $GLOBALS['_scfg']['db'][$key];
            $data = $secure->encode($db);
            $rs   = ['ret'=>$check,'data'=>$data];
        }else
        {
            $rs   = ['ret'=>$check,'error'=>$error_msg];
        }
        exit(json_encode($rs,JSON_UNESCAPED_UNICODE));
    }
}
