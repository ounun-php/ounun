<?php
/** 命名空间 */

namespace app\adm\controller;

use app\adm\model\purview;
use extend\config_cache;

class index extends adm
{
    /** 开始init */
    public function index($mod)
    {
        // print_r(['$_SESSION'=>$_SESSION]);
        if (self::$auth->login_check()) {
            $this->init_page('/', false, true, '', 0, false);
            $this->_nav_data_set();

            $scfg_cache = config_cache::instance(\c::Cache_Tag_Site, self::$db_biz);
            $site0 = $scfg_cache->site();

            $site = [];
            foreach ($site0 as $k2 => $v2) {
                foreach ($v2 as $k => $v) {
                    if ($v['status']) {
                        $site[$v['zqun_tag']][] = [
                            'k' => $v['site_tag'],
                            'name' => $v['name'],
                            'type' => $v['type'],
                            'domain' => $v['main_domain'],
                        ];
                    }
                }
            }

            $zqun0 = $scfg_cache->zqun();

            $zqun = [];
            foreach ($zqun0 as $v) {
                if ($site[$v['zqun_tag']]) {
                    $zqun[$v['zqun_tag']] = $v['name'];
                }
            }
            //  print_r(['$zqun'=>$zqun,'$site0'=>$site0,'$site'=>$site]);
            $scfg = ['site' => $site, 'zqun' => $zqun];

            require \v::tpl_fixed('index.html.php');
        } else {
            // 还没登录
            go_url('/login.html');
        }
    }

    /** 登录Post */
    public function login($mod)
    {
        // ---------------------------------------
        // 登录
        if ($_POST) {
            $this->_login_post($_POST);
            exit();
        }
        // ---------------------------------------
        // 显示页面
        if (self::$auth->login_check()) {
            // 登录了
            go_url('/');
        } else {
            // 还没登录
            $this->init_page('/login.html', false, true, '', 0, false);
            $this->_nav_data_set();
            require \v::tpl_fixed('login.html.php');
        }
    }

    private function _login_post($args)
    {
        $rs = self::$auth->login($args['admin_username'], $args ['admin_password'], (int)$args['admin_cid'], $args ['admin_google']);
        if (error_is($rs)) {
            echo msg($rs['message']);
            go_url('/', false, 302, 2);
        } else {
            go_url('/');
        }
    }

    /** 退出登录 */
    public function out($mod)
    {
        self::$auth->logout();
        go_url('/login.html');
    }

    /** 权限受限 */
    public function no_access($mod)
    {
        $this->init_page('/no_access.html', false, true, '', 0, false);
        $this->_nav_data_set();

        //  echo $this->require_file('sys/no_access.html.php' );
        require \v::tpl_fixed('sys_adm/no_access.html.php');
    }


    /** 站点类型有误 */
    public function error_site_type($mod)
    {
        $this->init_page('/error_site_type.html', false, true, '', 0, false);

        $this->_nav_data_set('站点类型有误', '系统', (int)$_GET['nav']);

        require \v::tpl_fixed('sys_adm/error_site_type.html.php');
    }

    /** 提示 没有选择平台 与 服务器 */
    public function select_tip($mod)
    {
        $this->init_page('/select_tip.html', false, true, '', 0, false);

        $nav = (int)$_GET['nav'];
        $title = $_GET['title'] ? $_GET['title'] : '系统';
        $title_sub = $_GET['title_sub'] ? $_GET['title_sub'] : '请选择正确的参数';
        $this->_nav_data_set($title_sub, $title, $nav);
        require \v::tpl_fixed('sys_adm/select_tip.html.php');
    }

    /** 设定当前平台 与服务器 */
    public function select_set($mod)
    {
        $url = '';
        if (isset($_GET['uri'])) {
            $url = $_GET['uri'];
            unset($_GET['uri']);
        }

        foreach ($_GET as $k => $v) {
            if ('adm_' == substr($k, 0, 4)) {
                self::$auth->cookie_set($k, $v);
            }
        }

        if ($url) {
            go_url($url);
        }
    }

    /**
     * 显示认证码
     * @param $mod
     */
    public function captcha($mod)
    {
        // \plugins\captcha\Captcha::output();
        \plugins\captcha\cookie::output();
    }
}