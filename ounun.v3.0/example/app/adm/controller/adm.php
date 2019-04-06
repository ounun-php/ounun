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
                $sites3 = $sites2[$adm_site];
                if ($sites3 && purview::app_type_site == $sites3['type'] && $adm_site == $sites3['site_tag']) {
                    $this->_site_type = $sites3['type'];
                    if ($sites3['dns']) {
                        $db_dns0 = json_decode($sites3['dns'], true);
                        if ($db_dns0) {
                            $db_dns = [];
                            foreach ($db_dns0 as $v) {
                                $db_dns[$v['tag']] = $v;
                            }
                            $GLOBALS['_site']['dns'] = $db_dns;
                        }
                    }
                    if ($sites3['db']) {
                        $db_cfg = json_decode($sites3['db'], true);
                        if ($db_cfg && $db_cfg['host']) {
                            static::$db_site = pdo::instance('site', $db_cfg);
                        } else {
                            static::$db_site = static::$db_biz;
                        }
                    } else {
                        static::$db_site = static::$db_biz;
                    }
                } elseif ($sites3 && purview::app_type_admin == $sites3['type']) {
                    static::$db_site = static::$db_biz;
                } else {
                    static::$db_site = static::$db_biz;
                }
            }
            // print_r(['$sites2'=>$sites2,'$sites'=>$sites]);
        }

        // adm_purv -----------------
        $adm_libs = static::$auth->cookie_get(purview::adm_caiji_tag);
        if ($adm_libs) {
            $libs = config::$global['libs'][$adm_libs];
            if ($libs && $libs['db']) {
                static::$db_caiji = pdo::instance('caiji', $libs['db']);
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
        if (purview::nav_libs == $nav) {
            $libs_key = static::$auth->cookie_get(purview::adm_caiji_tag);
            $title_sub = '请选择"资料库"';
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