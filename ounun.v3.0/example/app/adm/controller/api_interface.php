<?php

namespace app\adm\controller;

use extend\config_cache;
use ounun\config;
use ounun\mvc\model\admin\secure;
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
        $this->_nav_purview_check('api_interface/mysql.html', 'site@site_list', '接口', '系统', purview::nav_null);

        $table = ' `sys_site_info` ';
        $site_info = null;
        if ($_GET['site_tag']) {
            $site_info = static::$db_biz->table($table)
                ->field('*')
                ->where('`site_tag` = :site_tag', ['site_tag' => $_GET['site_tag']])
                ->column_one();
        }

        if ($site_info) {
            $api_host = $site_info['api'];
            if ($api_host) {
                $secure = new secure(config::$app_key_communication);
                $url = $secure->url("{$api_host}/api/interface_mysql.html", ['release' => Environment ? 0 : 1]);
                // echo "\$url:{$url}<br />\n";
                $c = \plugins\curl\http::file_get_contents($url);
                if ($c) {
                    $json = json_decode($c, true);
                    if(error_is($json)){
                        $error_msg = error_message($json);
                    }elseif ($json && $json['data']) {
                        $data = $secure->decode($json['data']);
//                        print_r([
//                            '$data' => $data
//                        ]);
                        if ($data) {
                            $bind = [
                                'config_db' => is_array($data) ? json_encode($data, JSON_UNESCAPED_UNICODE) : $data,
                            ];
                            $rs = static::$db_biz->table($table)
                                ->where(' `site_tag` = :site_tag ', ['site_tag' => $_GET['site_tag']])
                                ->update($bind);
                            static::$db_biz->stmt()->debugDumpParams();
                            if ($rs) {
                                config_cache::instance(\c::Cache_Tag_Site,static::$db_biz)->site_clean();
                            }
                            $url_back = "/site/site_add.html?site_tag={$_GET['site_tag']}";
                            go_url($url_back);
                        } else {
                            $error_msg = "出错:解码出错({$json['data']})";
                        }
                    } else {
                        $error_msg = "出错:数据出错({$c})";
                    }
                } else {
                    $error_msg = "出错:服务器没反:({$api_host})";
                }
            } else {
                $error_msg = "出错:网站api:({$_GET['site_tag']})";
            }
        } else {
            unset($_GET['site_tag']);
            $error_msg = "出错:找不到网站标识:({$_GET['site_tag']})";
        }
        $url_back = '/site/site_list.html';
        if ($_GET['site_tag']) {
            $url_back = "/site/site_add.html?site_tag={$_GET['site_tag']}";
        }
        // go_msg($error_msg, $url_back);
    }
}
