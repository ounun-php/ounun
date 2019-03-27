<?php
/** 命名空间 */
namespace app\adm\controller;

use app\adm\model\purview;
use ounun\pdo;

class index extends adm
{
    /** 开始init */
	public function index($mod)
	{
        // print_r(['$_SESSION'=>$_SESSION]);
		if (self::$auth->login_check())
		{
            $this->init_page('/',false,true,'',0,false);
            $this->_nav_set_data();

            $db     = pdo::instance( 'adm' );
            $cid    = self::$auth->session_get(purview::session_cid);

            require \v::tpl_fixed('index.html.php');
		}else
		{
		    // 还没登录
            go_url ('/login.html');
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
		if ( self::$auth->login_check() ) {
		    // 登录了
            go_url ('/');
		}else {
		    // 还没登录
            $this->init_page('/login.html',false,true,'',0,false);
            $this->_nav_set_data();
            require \v::tpl_fixed('login.html.php');
		}
	}

	private function _login_post($args)
	{
        $rs = self::$auth->login($args['admin_username'],$args ['admin_password'],(int)$args['admin_cid'],$args ['admin_google']);
        if(error_is($rs)){
            echo msg ( $rs['message']);
            go_url ('/',false,302,2);
        }else {
            go_url ('/');
        }
	}

	/** 退出登录 */
	public function out($mod)
	{
        self::$auth->logout();
        go_url('/login.html' );
	}

	/** 权限受限 */
	public function no_access($mod)
	{
        $this->init_page('/no_access.html',false,true,'',0,false);
        $this->_nav_set_data();

        //  echo $this->require_file('sys/no_access.html.php' );
        require require \v::tpl_fixed('sys_adm/no_access.html.php' );
	}

	/** 提示 没有选择平台 与 服务器 */
	public function select_tip($mod)
	{
        $this->init_page('/select_tip.html',false,true,'',0,false);
		
		$nav        = (int)$_GET['nav'];
        $title      = $_GET['title']?$_GET['title']:'系统';
        $title_sub  = $_GET['title_sub']?$_GET['title_sub']:'请选择正确的参数';
        $this->_nav_set_data($title_sub,$title,$nav);
        require \v::tpl_fixed( 'sys_adm/select_tip.html.php' );
	}

	/** 设定当前平台 与服务器 */
	public function select_set($mod)
	{
		if(isset($_GET['cid'])) {
		    self::$auth->cookie_set(purview::adm_cid,$_GET['cid']);
		}
		if (isset($_GET['sid'])) {
            self::$auth->cookie_set(purview::adm_sid,$_GET['sid']);
		}
		if (isset($_GET['uri'])) {
            go_url($_GET['uri']);
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