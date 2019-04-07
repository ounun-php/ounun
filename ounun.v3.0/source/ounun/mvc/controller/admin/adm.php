<?php

namespace ounun\mvc\controller\admin;
/**
 * Created by PhpStorm.
 * User: dreamxyp
 * Date: 2018/12/9
 * Time: 10:39
 */

use \ounun\mvc\model\admin\oauth;
use ounun\mvc\model\admin\purview;
use \ounun\config;
use ounun\pdo;

/********************************************************************
 * 后台基类
 ********************************************************************/
abstract class adm extends \v
{
    /** @var purview */
    public static $purview;
    /** @var oauth */
    public static $auth;
    /** @var adm */
    public static $instance;

    /** @var \ounun\pdo */
    public static $db_adm;
    /** @var \ounun\pdo */
    public static $db_biz;

    /** @var \ounun\pdo */
    public static $db_caiji;
    /** @var \ounun\pdo */
    public static $db_site;

    /**
     * @param string $db_tag
     * @param array $db_config
     * @return pdo
     */
    public static function db(string $db_tag = 'adm', array $db_config = [])
    {
        if ('adm' == $db_tag) {
            if (empty(static::$db_adm)) {
                static::$db_adm = pdo::instance($db_tag, $db_config);
            }
            return static::$db_adm;
        } elseif ('biz' == $db_tag) {
            if (empty(static::$db_biz)) {
                static::$db_biz = pdo::instance($db_tag, $db_config);
            }
            return static::$db_biz;
        } elseif ('caiji' == $db_tag) {
            if (empty(static::$db_caiji)) {
                static::$db_caiji = pdo::instance($db_tag, $db_config);
            }
            return static::$db_caiji;
        } elseif ('site' == $db_tag) {
            if (empty(static::$db_site)) {
                static::$db_site = pdo::instance($db_tag, $db_config);
            }
            return static::$db_site;
        }
        // 默认
        if (empty(static::$db_v)) {
            static::$db_v = pdo::instance(config::database_default_get());
        }
        return static::$db_v;
    }

    /** @var string 站点类型 */
    protected $_site_type = 'admin';
    /** @var array */
    protected $_site_type_only = [];

    /**
     * 权限检测,没权限时就跳到 NoAccess
     * @param string $key
     */
    protected function purview_check($key, $nav = 0)
    {
        if (!self::$purview->check_multi($key)) {
            $data = [
                'nav' => $nav,
                'uri' => $_SERVER['REQUEST_URI']
            ];
            $url = url_build_query('/no_access.html', $data);
            go_url($url);  // 没权限就跳
        }

        if (!self::$auth->session_get(purview::session_google) && 'sys@google' != $key) {
            go_url('/sys_adm/google.html?nav=' . $nav);
        }
    }

    /**
     * 选服检测,没选时就跳到
     */
    abstract protected function select_check($nav);

    /**
     * 选服/权限检测
     * @param string $page
     * @param string $purview_key
     * @param string $page_title_sub
     * @param string $page_title
     * @param int $nav
     */
    protected function _nav_purview_check(string $page, string $purview_key, $page_title_sub = '系统', $page_title = '系统', $nav = 0)
    {
        // 权限
        $this->purview_check($purview_key, $nav);
        // 选服
        $this->select_check($nav);
        // print_r($this->_replace_data);
        $this->init_page($page, false, true, '', 0, false);
        //
        $this->_nav_data_set($page_title, $page_title_sub, $nav);
    }

    /**
     * 设定数据
     * @param string $page_title_sub
     * @param string $page_title
     * @param int $nav
     */
    protected function _nav_data_set($page_title_sub = '系统', $page_title = '系统', $nav = 0)
    {
        $cfg_name = self::$purview->config_name[$_SERVER['HTTP_HOST']];
        $cfg_name = $cfg_name ? $cfg_name : self::$purview->config_name['adm2'];
        // 标题
        $data = [
            '{$page_title}' => $page_title,
            '{$page_title_sub}' => $page_title_sub,
            '{$page_nav}' => $nav,

            '{$page_url}' => $_GET['uri'] ? $_GET['uri'] : $_SERVER['REQUEST_URI'],

            '{$site_name}' => $cfg_name['name'],
            '{$site_logo_dir}' => $cfg_name['dir'],
        ];

        config::template_array_set($data);
        config::template_array_set(self::$purview->config);
    }

    /**
     * @return string
     */
    public function site_type()
    {
        return $this->_site_type;
    }
}
