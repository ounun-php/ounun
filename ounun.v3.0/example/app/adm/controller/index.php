<?php
/** 命名空间 */
namespace controller;

use model\purview;

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

            $db     = self::db ( 'adm' );
            $cid    = self::$auth->session_get(purview::s_cid);

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
		if ($_POST)
		{
			$this->_login_post($_POST);
			exit();
		}
		// ---------------------------------------
		// 显示页面
		if ( self::$auth->login_check() )
		{
		    // 登录了
            go_url ('/');
		}else
		{
		    // 还没登录
            $this->init_page('/login.html',false,true,'',0,false);
            $this->_nav_set_data();

            require \v::tpl_fixed('login.html.php');
		}
	}

	private function _login_post($args)
	{
        $rs = self::$auth->login($args['admin_username'],$args ['admin_password'],(int)$args['admin_cid'],$args ['admin_google']);
		if ($rs->ret )
		{
            // var_dump($_SESSION);
		    // var_dump($rs);
            go_url ('/');
		}else
		{
			echo msg ( $rs->data);
            go_url ('/',false,302,2);
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
		
		$nav  = (int)$_GET['nav'];
		if(1 == $nav &&  0 == self::$auth->session_get(purview::s_cid) )
		{
			$title_sub = '请选择“平台”';
		}
		elseif(2 == $nav && 0 == self::$auth->session_get(purview::s_cid)  )
		{
			$title_sub = '请选择“平台”与“服务器”';
		}elseif(2 == $nav)
		{
			$title_sub = '请选择“服务器”';
		}
        $this->_nav_set_data($title_sub,'系统',0);
        require \v::tpl_fixed( 'sys/select_tip.html.php' );
	}

	/** 设定当前平台 与服务器 */
	public function select_set($mod)
	{
		if(isset($_GET['cid']))
		{
		    self::$auth->cookie_set(purview::cp_cid,$_GET['cid']);
		}
		if (isset($_GET['sid']))
		{
            self::$auth->cookie_set(purview::cp_sid,$_GET['sid']);
		}
		if (isset($_GET['uri']))
		{
            go_url($_GET['uri']);
		}
	}

	/** 显示认证码 */
	public function captcha($mod)
	{
		// \plugins\captcha\Captcha::output();
		\plugins\image\captcha::output();
	}
}