<?php
namespace app\adm\controller;

use extend\config_cache;
use ounun\config;
use app\adm\model\purview;
use ounun\pdo;

class adm extends \ounun\mvc\controller\admin\adm
{
    public function __construct($mod)
    {
        // 初始化
        $this->_db_adm  = pdo::instance('adm');
        self::$auth     = \ounun\mvc\model\admin\oauth::instance($this->_db_adm,new purview(),'mk8');

        $cp_site        = self::$auth->cookie_get(purview::cp_site);
        $cp_zqun        = self::$auth->cookie_get(purview::cp_zqun);
        if($cp_site && $cp_zqun)
        {
            $db_libs    = pdo::instance('adm');
            $sites      = config_cache::instance($db_libs)->site();
            $sites2     = $sites[$cp_zqun];
            if($sites2 && is_array($sites2))
            {
                $sites3 = $sites2[$cp_site];
                if($sites3 && purview::app_type_site == $sites3['type'] && $cp_site == $sites3['site_tag'])
                {
                    $this->_site_type                = $sites3['type'];
                    if($sites3['dns'])
                    {
                        $db_dns0                     = json_decode($sites3['dns'],true);
                        if($db_dns0)
                        {
                            $db_dns                  = [];
                            foreach ($db_dns0 as $v)
                            {
                                $db_dns[$v['tag']]   = $v;
                            }
                            $GLOBALS['_site']['dns'] = $db_dns;
                        }
                    }
                    if($sites3['db'])
                    {
                        $db_cfg               = json_decode($sites3['db'],true);
                        if($db_cfg && $db_cfg['host'])
                        {
                            $this->_db_site   = pdo::instance($cp_site,$db_cfg);
                        }else{$this->_db_site = $this->_db_adm;}
                    }else{$this->_db_site     = $this->_db_adm;}
                }elseif ($sites3 && purview::app_type_admin == $sites3['type'])
                {
                    $this->_db_site   = $db_libs;
                }else{$this->_db_site = $this->_db_adm;}
            }
            // print_r(['$sites2'=>$sites2,'$sites'=>$sites]);
        }

        // adm_purv -----------------
        $cp_libs        = self::$auth->cookie_get(purview::cp_libs);
        if($cp_libs)
        {
            $libs = config::$global['libs'][$cp_libs];
            if($libs && $libs['db'])
            {
                $this->_db_libs   = pdo::instance($libs['db']);
            }else{$this->_db_libs = $this->_db_adm;}
        }

        parent::__construct($mod);
    }

    public function purview_check($key, $nav = 0)
    {
        parent::purview_check($key, $nav);
        //
        if($this->_site_type_only  && !in_array($this->_site_type,$this->_site_type_only))
        {
            $data = [
                'nav'            => purview::nav_site,
                'uri'            => $_SERVER['REQUEST_URI'],
                'site_type_only' => implode(',',$this->_site_type_only)
            ];
            $url = url_build_query('/error_site_type.html',$data);
            go_url($url);
        }
    }

    public function select_check($nav)
    {
        parent::select_check($nav);
        //
        $uri   = url_original($_SERVER['REQUEST_URI']);
        if (purview::nav_libs == $nav)
        {
            $libs_key = self::$auth->cookie_get(purview::cp_libs);
            if (!$libs_key)
            {
                go_url("/select_tip.html?nav={$nav}&uri={$uri}");
            }
        }elseif (purview::nav_site == $nav)
        {
            $zqun_key = self::$auth->cookie_get(purview::cp_zqun);
            $site_key = self::$auth->cookie_get(purview::cp_site);
            if (!$zqun_key || !$site_key)
            {
                go_url("/select_tip.html?nav={$nav}&uri={$uri}");
            }
        } // if
    }

    /**
     * @return array
     */
    public function libs_tables()
    {
        $cp_libs         = self::$auth?self::$auth->cookie_get(purview::cp_libs):$_COOKIE[purview::cp_libs];
        $pics            = [];
        if($cp_libs) {
            $tables      = config::$global['libs'][$cp_libs];
            if($tables && $tables['table'] && is_array($tables['table']) ) {
                $pics    = $tables['table'];
            }
        }
        return $pics;
    }
}