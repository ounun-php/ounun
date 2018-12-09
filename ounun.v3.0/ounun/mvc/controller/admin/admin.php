<?php
namespace ounun\mvc\controller\admin;
/**
 * Created by PhpStorm.
 * User: dreamxyp
 * Date: 2018/12/9
 * Time: 10:39
 */

/********************************************************************
 * 后台基类
 ********************************************************************/
class adm extends \v
{
    /** @var \admin\oauth */
    public static $auth;

    /** @var \ounun\mysqli */
    protected $_db_adm;

    public function __construct($mod)
    {
        // 初始化
        $this->_db_adm  = self::db('adm');
        self::$auth     = new \admin\oauth($this->_db_adm,new purview_adm(),'irc');
        // 执行
        parent::__construct($mod);
    }

    /**
     * 权限检测,没权限时就跳到 NoAccess
     * @param string $key
     * @return boolean
     */
    protected function purview_check($key)
    {
        //echo 'this is a test'.$key;
        if( !self::$auth->purview->check_multi($key) )
        {
            // 没权限就跳
            \ounun::go_url('/no_access.html');
        }

        if(!self::$auth->session_get(\admin\purview::s_google) && 'sys@google' != $key)
        {
            \ounun::go_url('/sys_adm/google.html');
        }
    }

    /**
     * 选服检测,没选时就跳到
     */
    protected function select_check($nav)
    {
        if(1 == $nav)
        {
            $cid  = self::$auth->cookie_get(\admin\purview::cp_cid);
            if (!$cid)
            {
                $uri = \ounun::url_original($_SERVER['REQUEST_URI']);
                \ounun::go_url("/select_tip.html?nav={$nav}&uri={$uri}");
            }
        }
        elseif (2 == $nav)
        {
            $cid  = self::$auth->cookie_get(\admin\purview::cp_cid);
            $sid  = self::$auth->cookie_get(\admin\purview::cp_sid);
            if (!$cid || !$sid)
            {
                $uri = \ounun::url_original($_SERVER['REQUEST_URI']);
                \ounun::go_url("/select_tip.html?nav={$nav}&uri={$uri}");
            }
        }
    }

    /**
     * 选服/权限检测
     *
     * @param string $purview_key
     * @param int    $nav
     * @return bool
     */
    protected function _nav_pur_check(string $page,string $purview_key,$page_title_sub = '默认标题子类', $page_title = '默认标题', $nav = 0)
    {
        $data = [
            '{$page_title}'     => $page_title,
            '{$page_title_sub}' => $page_title_sub,
            '{$page_nav}'       => $nav,
        ];
        $this->replace_sets($data);
        $this->_nav_set_data();
        // 权限
        $this->purview_check($purview_key);
        // 选服
        $this->select_check($nav);
        //

        // print_r($this->_replace_data);
        $this->init_page($page,false,true);
    }

    /**
     * 设定数据
     */
    protected function _nav_set_data()
    {
        $cfg_name = self::$auth->purview->cfg_name[$_SERVER['HTTP_HOST']];
        $cfg_name = $cfg_name?$cfg_name:self::$auth->purview->cfg_name['adm3.happyuc.org'];
        // 标题
        $data = [
            '{$page_url}'       => $_GET['uri'] ? $_GET['uri'] : $_SERVER['REQUEST_URI'],

            '{$site_name}'      => $cfg_name['name'],
            '{$site_logo_dir}'  => $cfg_name['dir'],
        ];

        $this->replace_sets($data);
        $this->replace_sets(self::$auth->purview->cfg);
    }
}