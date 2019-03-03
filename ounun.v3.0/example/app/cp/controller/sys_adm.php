<?php
namespace app\cp\controller;

use app\adm\model\purview;

class sys_adm extends adm
{
    /**
     * 管理员 - 密码更新
     * @param array $mod
     */
    public function password($mod)
    {
        // 权限
        $this->_nav_pur_check('sys_adm/password.html','sys@password', '密码更新','管理员',purview::nav_null);

        // 修改密码
        if($_POST)
        {
            $rs         = self::$auth->user_modify_passwd($_POST['oldpwd'],$_POST['newpwd'],$_POST['google']);
            echo msg($rs->data);
            go_back();
        }
        else
        {
            require \v::tpl_fixed('sys_adm/sys_password.html.php');
        }
    }

    /**
     * 管理员 - Google身份验证
     * @param array $mod
     */
    public function google($mod)
    {
        // 权限/选服
        $this->_nav_pur_check('sys_adm/google.html','sys@google', '谷歌(洋葱)动态验证','管理员',purview::nav_null);

        // 修改密码
        if($_POST)
        {
            if('set' == $_POST['act'])
            {
                //  设定 Google身份验证
                $rs = self::$auth->user_set_exts_google(true,null,$_POST['password'],$_POST['google']);
            }elseif('del' == $_POST['act'])
            {
                //  删除 Google身份验证
                $rs = self::$auth->user_set_exts_google(false,null,$_POST['password'],$_POST['google']);
            }
            // 跳回原来的页面
            echo msg($rs->data);
            // 跳回原来的页面
            go_back();
        }
        else
        {
            $ext  = self::$auth->user_get_exts();
            // 赋值
            // $this->assign('ext',			$ext);
            require \v::tpl_fixed('sys_adm/sys_google.html.php');
        }
    }

    /**
     * 管理员 - 欢迎
     * @param array $mod
     */
    public function welcome($mod)
    {
        // 权限/选服
        $this->_nav_pur_check('sys_adm/welcome.html','sys@google', '欢迎','管理员',purview::nav_null);

        require \v::tpl_fixed('sys_adm/sys_welcome.html.php');
    }

	/**
	 * 添加管理人员
	 * @param array $mod
	 */
	public function adm_add($mod)
	{
        // 权限/选服
        $this->_nav_pur_check('sys_adm/adm_add.html','sys@adm_add', '添加管理人员','管理员管理',purview::nav_null);

		// 插入管理员
		if($_POST)
		{
            $rs = self::$auth->user_add((int)$_POST['adm_type'],(int)$_POST['adm_cid'],(string)$_POST['adm_account'],(string)$_POST['password'],(string)$_POST['adm_tel'],(string)$_POST['adm_note']);
            echo msg($rs->data);
            go_back();
		}
		/////////////////////////////////////////////////////////////////////////
        // 自己的权限
        $adm_type      = self::$auth->session_get(purview::session_type); // get_type();
		$purview_group = [];
        $purview_show  = [];
		foreach (self::$auth->purview->purview_group as $k=>$v)
		{
		    // echo "\$k:{$k}=>\$v:{$v}\n";
		    // print_r(['$k'=>$k,'$v'=>$v,'$adm_type'=>$adm_type]);
			if($k >= $adm_type)
			{
				$purview_group[$k] = $v;
                $purview_show[$k]  = $this->_adm_add_show($k);
			}
		}

        require \v::tpl_fixed('sys_adm/sys_adm_add.html.php');
	}



    /*
    * 显示权限
    */
    protected function _adm_add_show($type)
    {
        $rs           = '';
        $uuid         = 0;
        $purview      = self::$auth->purview->data($type);
        if($purview && is_array($purview))
        {
            foreach ( $purview as $key1 => $data1 )
            {
                $rs .= '<h4 style="color: blue;">' . $data1 ['name'] . '</h4>';
                foreach ( $data1 ['sub'] as $key2 => $data2 )
                {
                    if ($data2 ['url'])
                    {
                        $rs .= $data2 ['name'] . ', ';
                    } else
                    {
                        $uuid ++;
                        $i   = 0;
                        foreach ( $data2 ['data'] as $key3 => $data3 )
                        {
                            $i ++;
                            if (0 == $i % 5)
                            {
                                $rs .= '<br />';
                            }
                            if ($data3 ['name'])
                            {
                                $rs .= $data3 ['name'] . ', ';
                            }
                        }
                    }
                }
            }
        }
        return $rs;
    }

	
	/**
	 * 管理员列表
	 * @param array $mod
	 */
	public function adm_list($mod)
	{
        // 权限
        $this->_nav_pur_check('sys_adm/adm_list.html','sys@adm_list', '管理员列表','管理员管理',purview::nav_null);

		//if (isset($_GET['del']) && isset($_GET['id']))
		if ('del' == $_GET['act'] && $_GET['adm_id'])
		{
            $rs   = self::$auth->user_del($_GET['adm_id']);
            echo msg($rs->data);
            go_back();
		}

        // 列表
        $user_rs    = $this->_db_adm->data_array("SELECT * FROM  ".self::$auth->purview->db_adm." order by `adm_id` ASC ;");
        // 哈哈
        $user_list  = [];
        foreach ($user_rs as $v)
        {
            $exts        = self::$auth->user_get_exts($v,$v['adm_id']);
            // print_r($exts);
            $user_list[] = [
                'adm_id' 		=> $v['adm_id'],
                'type'			=> $v['type'],
                'tname'			=> self::$auth->purview->purview_group[$v['type']],
                'cid'			=> $v['cid'],
                // 'cname'		=> $platform_list[$v['cid']],
                'account'		=> $v['account'],
                'login_times'	=> $v['login_times'],
                'login_last'	=> $v['login_last'],
                'tel'	        => $v['tel'],
                'google_is'     => $exts['google']['is']?'开启':'-',
                'note'			=> $v['note'],
            ];
        }
        //
//      $this->assign('user_list',		$user_list);
//      $this->output('sys_adm/sys_adm_list.html.php');

        require \v::tpl_fixed('sys_adm/sys_adm_list.html.php');
	}

	/**
	 * 操作日志
	 * @param array $mod
	 */
	public function logs_act($mod)
	{
        // 权限
        $this->_nav_pur_check('sys_adm/logs_act.html','sys@logs_act', '操作日志','管理员日志',purview::nav_null);

        $table   = self::$auth->purview->db_logs_act;
        if ($_GET['act'] == 'del')
        {
            $this->_db_adm->delete($table,'`id`= :id ',$_GET);
            // 跳回原来的页面
            go_back();
        }

        $where   = [];
        $where[] = ' `status` =:status ';
        if ($_GET['account'])
        {
            $where[]			 = ' `account` =:account ';
        }
        if ($_GET['mod'])
        {
            $where[]			 = ' `mod` =:mod ';
        }
        if ($_GET['mod_sub'])
        {
            $where[]			 = ' `mod_sub` =:mod_sub ';
        }
        if ($_GET['act'])
        {
            $where[]			 = ' `act` =:act ';
        }

        $where = $where?' where '.implode(' and ', $where):'';
        /** 分页 */
        $page       = (int)$_GET['page'];
        $page       =      $page>1?$page:$page;
        $rows       = 20;
        $where_bind = $_GET;



        $url     = url( url_original(),$_GET,['page'=>'{page}']);

        $pg      = new \ounun\page($this->_db_adm,$table,$url,$where,$where_bind,'count(*)',\status::page_cfg,$rows);
        $ps      = $pg->init($page,"");

        $data	 = $this->_db_adm->data_array("select * from {$table} {$where} ORDER BY `id` DESC limit {$pg->limit_start()},{$rows}", $where_bind);

        require \v::tpl_fixed('sys_adm/sys_logs_act.html.php');
	}

	/**
	 * 登录日志
	 * @param array $mod
	 */
	public function logs_login($mod)
	{
        // 权限
        $this->_nav_pur_check('sys_adm/logs_login.html','sys@logs_login', '登录日志','日志',purview::nav_null);

        $table   = self::$auth->purview->db_logs_login; //$this->table_logs_login;
		if ($_GET['act'] == 'del')
		{
            $this->_db_adm->delete($table,'`id`= :id ',$_GET);
			// 跳回原来的页面
            go_back();
		}

        $where           = [];
        $where[]         = ' `status` =:status ';
        $_GET['status']  = (int)$_GET['status'];
        if ($_GET['account'])
        {
            $where[]			 = ' `account` =:account ';
        }

        $where = $where?' where '.implode(' and ', $where):'';
        /** 分页 */
        $page       = (int)$_GET['page'];
        $page       =      $page>1?$page:$page;
        $rows       = 20;
        $where_bind = $_GET;

        $url     = url( url_original(),$_GET,['page'=>'{page}']);

        $pg      = new \ounun\page($this->_db_adm,$table,$url,$where,$where_bind,'count(*)',\status::page_cfg,$rows);
        $ps      = $pg->init($page,"");

        $data	 = $this->_db_adm->data_array("select * from {$table} {$where} ORDER BY `id` DESC limit {$pg->limit_start()},{$rows}", $where_bind);

        // echo $this->_db_adm->sql()."\n";

        require \v::tpl_fixed('sys_adm/sys_logs_login.html.php');
	}
}
