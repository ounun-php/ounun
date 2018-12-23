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
use \ounun\scfg;

/********************************************************************
 * 后台基类
 ********************************************************************/
class admin extends \v
{
    /** @var oauth */
    public static $auth;

    /** @var \ounun\mysqli */
    protected $_db_adm;
    /** @var \ounun\mysqli */
    protected $_db_site;
    /** @var \ounun\mysqli */
    protected $_db_libs;

    /** @var string 站点类型 */
    protected $_site_type      = 'admin';

    protected $_site_type_only = [];

    /**
     * 权限检测,没权限时就跳到 NoAccess
     * @param string $key
     * @return boolean
     */
    protected function purview_check($key,$nav=0)
    {
        if( !self::$auth->purview->check_multi($key) )
        {
            $data = [
                'nav' => $nav,
                'uri' => $_SERVER['REQUEST_URI']
            ];
            $url =  url('/no_access.html',$data);
            go_url($url);  // 没权限就跳
        }

        if(!self::$auth->session_get( purview::s_google) && 'sys@google' != $key)
        {
            go_url('/sys_adm/google.html?nav='.$nav);
        }
    }

    /**
     * 选服检测,没选时就跳到
     */
    protected function select_check($nav)
    {

    }

    /**
     * 选服/权限检测
     *
     * @param string $purview_key
     * @param int    $nav
     * @return bool
     */
    protected function _nav_pur_check(string $page,string $purview_key,$page_title_sub = '系统', $page_title = '系统', $nav = 0)
    {
        // 权限
        $this->purview_check($purview_key,$nav);

        // 选服
        $this->select_check($nav);

        // print_r($this->_replace_data);
        $this->init_page($page,false,true,'',0,false);

        $this->_nav_set_data($page_title,$page_title_sub,$nav);
    }

    /**
     * 设定数据
     */
    protected function _nav_set_data($page_title_sub= '系统',$page_title= '系统',$nav = 0)
    {
        $cfg_name = self::$auth->purview->cfg_name[$_SERVER['HTTP_HOST']];
        $cfg_name = $cfg_name?$cfg_name:self::$auth->purview->cfg_name['adm2'];
        // 标题
        $data = [
            '{$page_title}'     => $page_title,
            '{$page_title_sub}' => $page_title_sub,
            '{$page_nav}'       => $nav,

            '{$page_url}'       => $_GET['uri'] ? $_GET['uri'] : $_SERVER['REQUEST_URI'],

            '{$site_name}'      => $cfg_name['name'],
            '{$site_logo_dir}'  => $cfg_name['dir'],
        ];

        scfg::set_tpl_array($data);
        scfg::set_tpl_array(self::$auth->purview->cfg);
    }
}
