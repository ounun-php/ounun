<?php
namespace app\adm\controller;

use ounun\mvc\model\admin\secure;
use extend\cache_config;
use app\adm\model\purview;

/**
 * 同步接口
 * @author dreamxyp
 */
class api_interface extends adm
{
    /**
     * 数据库同步接口
     * @param array $mod
     */
    public function mysql($mod)
    {
        // $this->_site_type_only = [purview::app_type_admin];
        $this->_nav_pur_check('api_interface/mysql.html','site@site_list', '接口','系统',purview::nav_null);

        // $db_libs         = self::db('libs');
        $table           = ' `adm_site_info` ';
        if($_GET['site_tag'])
        {
            $site_info = $this->_db_v->row("SELECT * FROM {$table} where `site_tag` = :site_tag ;", $_GET);
        }else
        {
            $site_info = null;
        }

        if($site_info)
        {
            $api_host= $site_info['api'];
            if($api_host)
            {
                $secure  = new secure(Const_Key_Conn_Private);
                $url     = $secure->url("https://{$api_host}/api/interface_mysql.html",['release'=>IsDebug?0:1]);
                $c       = @\plugins\curl\http::file_get_contents($url);
                if($c)
                {
                    $json    = json_decode($c,true);
                    if($json && $json['ret'] && $json['data'])
                    {
                        $data = $secure->decode($json['data']);
                        if($data)
                        {
                            $bind   = [
                                'db'  => is_array($data)  ?json_encode($data  ,JSON_UNESCAPED_UNICODE):$data,
                            ];
                            $rs = $this->_db_v->update($table,$bind," `site_tag` = :site_tag ",$_GET);
                            if($rs)
                            {
                                cache_config::instance($this->_db_v)->site_clean();
                            }
                            $url_back = "/site/site_add.html?site_tag={$_GET['site_tag']}";
                            go_url($url_back);
                        }else
                        {
                            $error_msg = "出错:解码出错:({$json['data']})";
                        }
                    }else
                    {
                        $error_msg = "出错:数据出错:({$c})";
                    }
                }else
                {
                    $error_msg = "出错:服务器没反:({$api_host})";
                }
            }else
            {
                $error_msg = "出错:网站api:({$_GET['site_tag']})";
            }
        }else
        {
            unset($_GET['site_tag']);
            $error_msg = "出错:找不到网站标识:({$_GET['site_tag']})";
        }
        $url_back = '/site/site_list.html';
        if($_GET['site_tag'])
        {
            $url_back = "/site/site_add.html?site_tag={$_GET['site_tag']}";
        }
        go_msg($error_msg,$url_back);
    }
}