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
        $url_interface = '/api/interface_mysql.html';
        list($data, $site_info) = $this->_interface_check($table, $url_interface);
        if (empty($data)) { exit('error:'.__METHOD__); }
        $bind = [
            'config_db' => is_array($data) ? json_encode($data, JSON_UNESCAPED_UNICODE) : $data,
        ];
        $rs = static::$db_biz->table($table)
            ->where(' `site_tag` = :site_tag ', ['site_tag' => $_GET['site_tag']])
            ->update($bind);
        // static::$db_biz->stmt()->debugDumpParams();
        if ($rs) {
            config_cache::instance(\c::Cache_Tag_Site, static::$db_biz)->site_clean();
        }
        $url_back = "/site/site_add.html?site_tag={$_GET['site_tag']}";
        go_url($url_back);
    }


    /**
     * 同步host配制
     * @param array $mod
     */
    public function hosts($mod)
    {
        // $this->_site_type_only = [purview::app_type_admin];
        $this->_nav_purview_check('api_interface/hosts.html', 'site@site_list', '接口', '系统', purview::nav_null);

        $table = ' `sys_site_info` ';
        $url_interface = '/api/interface_hosts.html';
        list($data, $site_info) = $this->_interface_check($table, $url_interface);
        if (empty($data)) { exit('error:'.__METHOD__); }
        $dns = [];
        $host = $site_info['host'];
        if ($site_info['dns']) {
            $dns0 = json_decode($site_info['dns'], true);
            foreach ($dns0 as $v) {
                $v['tag'] = $v['tag'] == 'pc' ? 'www' : $v['tag'];
                $dns[$v['tag']] = $v;
            }
            unset($dns0);
        }
        $bind = [];
        foreach ($data as $k => $v) {
            $tag = '';
            if ('domain_www' == $k) {
                $tag = 'www';
            } elseif ('domain_wap' == $k) {
                $tag = 'wap';
            } elseif ('domain_mip' == $k) {
                $tag = 'mip';
            } elseif ('domain_api' == $k) {
                $tag = 'api';
            } elseif ('domain_static' == $k) {
                $tag = 's';
                $v = explode('/', $v, 2)[0];
            }
            if ($tag) {
                $sub_domain = $v;
                $cdn = ($dns[$tag] && $dns[$tag]['cdn']) ? $dns[$tag]['cdn'] : $sub_domain;
                $bind[] = [
                    'tag' => $tag,
                    'sub_domain' => $sub_domain,
                    'cdn' => $cdn,
                    'host' => $host,
                ];
            }
        }
        //  print_r($bind);
        $bind = [
            'dns' => is_array($bind) ? json_encode($bind, JSON_UNESCAPED_UNICODE) : $bind,
        ];
        $rs = static::$db_biz->table($table)
            ->where(' `site_tag` = :site_tag ', ['site_tag' => $_GET['site_tag']])
            ->update($bind);
        // static::$db_biz->stmt()->debugDumpParams();
        if ($rs) {
            config_cache::instance(\c::Cache_Tag_Site, static::$db_biz)->site_clean();
        }
        $url_back = "/site/site_add.html?site_tag={$_GET['site_tag']}";
        go_url($url_back);
    }

    /**
     * 同步stat配制
     * @param array $mod
     */
    public function stat($mod)
    {
        // $this->_site_type_only = [purview::app_type_admin];
        $this->_nav_purview_check('api_interface/stat.html', 'site@site_list', '接口', '系统', purview::nav_null);

        $table = ' `sys_site_info` ';
        $url_interface = '/api/interface_stat.html';
        list($data, $site_info) = $this->_interface_check($table, $url_interface);
        if (empty($data)) { exit('error:'.__METHOD__); }

        $bind = [];
        foreach ($data as $k => $v) {
            $bind[] = [
                'tag' => $k,
                'stat_uid' => $v,
            ];
        }
        //  print_r($bind);
        $bind = [
            'config_stat' => is_array($bind) ? json_encode($bind, JSON_UNESCAPED_UNICODE) : $bind,
        ];
        $rs = static::$db_biz->table($table)
            ->where(' `site_tag` = :site_tag ', ['site_tag' => $_GET['site_tag']])
            ->update($bind);
        // static::$db_biz->stmt()->debugDumpParams();
        if ($rs) {
            config_cache::instance(\c::Cache_Tag_Site, static::$db_biz)->site_clean();
        }
        $url_back = "/site/site_add.html?site_tag={$_GET['site_tag']}";
        go_url($url_back);
    }

    /**
     * 同步seo配制
     * @param array $mod
     */
    public function seo($mod)
    {
        // $this->_site_type_only = [purview::app_type_admin];
        $this->_nav_purview_check('api_interface/seo.html', 'site@site_list', '接口', '系统', purview::nav_null);

        $table = ' `sys_site_info` ';
        $url_interface = '/api/interface_seo.html';
        list($data, $site_info) = $this->_interface_check($table, $url_interface);
        if (empty($data)) { exit('error:'.__METHOD__); }

        // print_r($data);
        $bind = [
            'config_seo' => is_array($data) ? json_encode($data, JSON_UNESCAPED_UNICODE) : $data,
        ];
        $rs = static::$db_biz->table($table)
            ->where(' `site_tag` = :site_tag ', ['site_tag' => $_GET['site_tag']])
            ->update($bind);
        // static::$db_biz->stmt()->debugDumpParams();
        if ($rs) {
            config_cache::instance(\c::Cache_Tag_Site, static::$db_biz)->site_clean();
        }
        $url_back = "/site/site_add.html?site_tag={$_GET['site_tag']}";
        go_url($url_back);
    }

    /**
     * @param string $table
     * @param string $url_interface
     * @return array
     */
    protected function _interface_check(string $table, string $url_interface)
    {
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
                $url = $secure->url("{$api_host}{$url_interface}", ['release' => Environment ? 0 : 1]);
                // echo "\$url:{$url}<br />\n";
                $c = \plugins\curl\http::file_get_contents($url);
                // echo "\$c:{$c}<br />\n";
                if ($c) {
                    $json = json_decode($c, true);
                    if (error_is($json)) {
                        $error_msg = error_message($json);
                    } elseif ($json && $json['data']) {
                        $data = $secure->decode($json['data']);
                        if ($data) {
                            return [$data, $site_info];
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
        return [null, $site_info];
    }
}
