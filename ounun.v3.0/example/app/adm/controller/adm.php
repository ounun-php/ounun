<?php

namespace app\adm\controller;

use extend\config_cache;
use ounun\config;
use app\adm\model\purview;
use ounun\mvc\model\admin\oauth;
use ounun\pdo;

class adm extends \ounun\mvc\controller\admin\adm
{
    public function __construct($mod)
    {
        //
        static::$instance = $this;
        // 初始化
        static::$db_adm = pdo::instance('adm');
        static::$db_biz = pdo::instance('biz');
        /** @var purview purview */
        static::$purview = new purview();
        /** @var oauth auth */
        static::$auth = \ounun\mvc\model\admin\oauth::instance('mk8');

        $adm_site = static::$auth->cookie_get(purview::adm_site_tag);
        $adm_zqun = static::$auth->cookie_get(purview::adm_zqun_tag);
        if ($adm_site && $adm_zqun) {
            $sites = config_cache::instance(\c::Cache_Tag_Site, self::$db_biz)->site();
            $sites2 = $sites[$adm_zqun];
            if ($sites2 && is_array($sites2)) {
                $site_info = $sites2[$adm_site];
                if ($site_info && purview::app_type_site == $site_info['type'] && $adm_site == $site_info['site_tag']) {
                    $this->_site_type = $site_info['type'];
                    if ($site_info['dns']) {
                        $db_dns0 = json_decode($site_info['dns'], true);
                        if ($db_dns0) {
                            $db_dns = [];
                            foreach ($db_dns0 as $v) {
                                if(isset($v['tag']) && $v['tag']){
                                    $db_dns[$v['tag']] = $v;
                                }
                            }
                            $GLOBALS['_site']['dns'] = $db_dns;
                        }
                    }
                    if ($site_info['config_db']) {
                        $db_cfg = json_decode($site_info['config_db'], true);
                        if ($db_cfg && $db_cfg['host']) {
                            static::$db_site = pdo::instance('site', $db_cfg);
                        } else {
                            static::$db_site = static::$db_biz;
                        }
                    } else {
                        static::$db_site = static::$db_biz;
                    }
                } elseif ($site_info && purview::app_type_admin == $site_info['type']) {
                    static::$db_site = static::$db_biz;
                } else {
                    static::$db_site = static::$db_biz;
                }
            }
            // print_r(['$sites2'=>$sites2,'$sites'=>$sites]);
        }

        // adm_purv -----------------
        $caiji_tag = static::$auth->cookie_get(purview::adm_caiji_tag);
        if ($caiji_tag) {
            $libs = config::$global['caiji'][$caiji_tag];
            if ($libs && $libs['db'] && config::$database[$libs['db']]) {
                static::$db_caiji = pdo::instance('caiji', config::$database[$libs['db']]);
            } else {
                static::$db_caiji = static::$db_biz;
            }
        }
        parent::__construct($mod);
    }

    public function purview_check($key, $nav = 0)
    {
        parent::purview_check($key, $nav);
        //
        if ($this->_site_type_only && !in_array($this->_site_type, $this->_site_type_only)) {
            $data = [
                'nav' => purview::nav_site,
                'uri' => $_SERVER['REQUEST_URI'],
                'site_type_only' => implode(',', $this->_site_type_only)
            ];
            $url = url_build_query('/error_site_type.html', $data);
            go_url($url);
        }
    }

    public function select_check($nav)
    {
        // REQUEST_URI
        $uri = url_original($_SERVER['REQUEST_URI']);
        if (purview::nav_caiji == $nav) {
            $libs_key = static::$auth->cookie_get(purview::adm_caiji_tag);
            $title_sub = '请选择"采集保存库"';
            // print_r(['$libs_key'=>$libs_key]);
            if (!$libs_key) {
                go_url("/select_tip.html?nav={$nav}&uri={$uri}&title_sub=" . urlencode($title_sub));
            }
        } elseif (purview::nav_site == $nav) {
            $zqun_key = static::$auth->cookie_get(purview::adm_zqun_tag);
            $site_key = static::$auth->cookie_get(purview::adm_site_tag);
            $title_sub = '请选择"站群"与"站点"';
            // print_r(['$zqun_key'=>$zqun_key,'$site_key'=>$site_key]);
            if (!$zqun_key || !$site_key) {
                go_url("/select_tip.html?nav={$nav}&uri={$uri}&title_sub=" . urlencode($title_sub));
            }
        } // if
    }

    /**
     * @return array
     */
    public function tables_caiji()
    {
        $adm_libs = static::$auth ? static::$auth->cookie_get(purview::adm_caiji_tag) : $_COOKIE[purview::adm_caiji_tag];
        $pics = [];
        if ($adm_libs) {
            $tables = config::$global['caiji'][$adm_libs];
            if ($tables && $tables['table'] && is_array($tables['table'])) {
                $pics = $tables['table'];
            }
        }
        return $pics;
    }
}