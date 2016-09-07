<?php
/** 命名空间 */
namespace adm;

//$GLOBALS['cp']['session']			  	  = 'cp_hy'; 	//管理id
//$GLOBALS['cp']['session_pass']		  = $GLOBALS['cp']['session'].'_pass';
//$GLOBALS['cp']['session_cid']		      = $GLOBALS['cp']['session'].'_cid';
//$GLOBALS['cp']['session_type']	  	  = $GLOBALS['cp']['session'].'_type';
//$GLOBALS['cp']['session_hash']		  = $GLOBALS['cp']['session'].'_hash';
class Auth
{
    /**
     * session key
     * @var string
     */
    private $_session_key  = '';
    private $_session_id   = '';
    private $_session_g    = '';
    private $_session_pass = '';
    private $_session_cid  = '';
    private $_session_type = '';
    private $_session_hash = '';



    /** IP限定 */
    private $_count_ips = 20;
    private $_count_ip  = 5;

    /** table */
    private $_table_cp    = '`sys_cp`';
    private $_table_logs  = '`logs_sys_cp`';
    /**
     * 权限列表
     * @var array
     */
    private $_purview           = [];
    private $_purview_default   = 'update';
    private $_purview_tree_root = [10,20];
    private $_purview_tree_coop = [10,20,50];
    /**
     * Mysqli 句柄
     * @var \ounun\Mysqli
     */
    private $_db;
    /**
     * Auth constructor.
     */
    public function __construct(\ounun\Mysqli $db,
                                $purview,
                                $purview_default   = 'update',
                                $purview_tree_coop = [10,20],$purview_tree_root=[10,20,50],
                                $session_key='adm',
                                $table_cp   = '`sys_cp`',   $table_logs   = '`logs_sys_cp`',
                                $count_ips  = 20,           $count_ip     = 5)
    {
        $this->_db          = $db;
        // purview
        $this->_purview             = $purview;
        $this->_purview_default     = $purview_default;
        $this->_purview_tree_coop   = $purview_tree_coop;
        $this->_purview_tree_root   = $purview_tree_root;
        // table
        $this->_table_cp    = $table_cp;
        $this->_table_logs  = $table_logs;
        // ip
        $this->_count_ip    = $count_ip;
        $this->_count_ips   = $count_ips;
        // session key
        $this->_session_key = $session_key;
        $this->__session_set();
    }


    /**
     * 是否登录
     * @return boolean
     */
    public function is()
    {
        $hash = $this->make_hash_rule($this->get_cid(), $this->get_account(), $this->get_pass() );
        if ($this->get_account()
            && $this->get_hash() == $hash)
        {
            return true;
        }
        return false;
    }

    /**
     * 登录
     * $field : id,type,cid,account,password,note
     */
    function login($account,$password,$cid,$code)
    {
        $check  = $this->login_check_ip($cid, $account);
        // 封帐号或IP
        if(!$check[0])
        {
            $status  = 0;
            $this->login_logs($status,$cid,$account);
            return $check;
        }
        // 正常登录
        $bind   = ['account'=>$account,'cid'=>$cid];
        $rs 	= $this->_db->row("select `id`,`type`,`exts`,`cid`,`account`,`password`,`login_times` from {$this->_table_cp} where `account` =:account and `cid` = :cid limit 1;",$bind);
        $ext    = unserialize($rs['exts']);
        if($ext && $ext['google'] && $ext['google']['is'])
        {
            $ext['google']['is'] = true;
        }else
        {
            $ext['google']['is'] = false;
        }
        //echo $db->getSql();
        //print_r($rs);
        //exit();
        //$password   	= $account;
        //$rs['type'] 	= 10;
        //$rs['cid']  	= $cid;
        //$rs['password'] = md5($password);
        //echo $rs['password'] ,'|',md5($password);
        //exit($rs['password']);
        if($rs)
        {
            if( !$ext['google']['is']
                || ($ext['google']['is'] && $this->login_google($ext, $code))
            )
            {
                if( $rs['password'] == md5($password) )
                {
                    // 清理一下
                    $this->login_out();
                    $this->set_cookie_cid($cid,true);
                    // 设定session
                    $hash = $this->__make_hash($cid,$account,$password);
                    $this->set_account($account);
                    $this->set_account_id($rs['id']);
                    $this->set_cid($cid);
                    $this->set_hash($hash);
                    $this->set_pass($password);
                    $this->set_type($rs['type']);
                    $this->set_google($ext['google']['is']);
                    // 返回
                    $login_times	= $rs['login_times'] + 1;
                    $login_last		= time();
                    $bind	= [
                        'login_times'=>$login_times,
                        'login_last' =>$login_last,
                    ];
                    $this->_db->update('`sys_cp`', $bind,' `id`= ? ',$rs['id']);
                    $status  = 1;
                    $this->login_logs($status,$cid,$account);
                    return array(true);
                }
                else
                {
                    $status  = 0;
                    $this->login_logs($status,$cid,$account);
                    return array(false,'失败:帐号或密码有误');
                }
            }else
            {
                $status  = 0;
                $this->login_logs($status,$cid,$account);
                return array(false,'失败:谷歌验证有误');
            }
        }
        else
        {
            $status  = 0;
            $this->login_logs($status,$cid,$account);
            return array(false,'失败:帐号不存在');
        }
    }
    /**
     * 记下登录日志
     * @param \ounun\Mysqli $db
     * @param int $status 返回状态 0:失败 1:成功
     * @param int $cid	合作方ID
     * @param string $account 用户帐号
     */
    function login_logs($status, $cid, $account)
    {
        $ip		= \ounun\ip();
        $wry    = new \plugins\qqwry\QQWry('utf-8');
        $uCity  = $wry->getlocation($ip);

        $time		= time();
        $ip_segment	= $uCity["beginip"] . "-" . $uCity["endip"];
        $address	= $uCity["country"];
        $bind	    = [
            'time'      =>$time,
            'status'    =>$status,
            'cid'       =>$cid,
            'account'   =>$account,
            'ip'        =>$ip,
            'ip_segment'=>$ip_segment,
            'address'   =>$address,
        ];
        $this->_db->insert('`logs_sys_cp`', $bind);
        // exit();
    }
    /**
     * IP检查
     * @param \ounun\Mysqli $db
     * @param int $cid
     * @param string $account
     */
    public function login_check_ip()
    {
        $ip		    = \ounun\ip();
        $wry        = new \plugins\qqwry\QQWry('utf-8');
        $uCity      = $wry->getlocation($ip);
        $ip_segment	= $uCity["beginip"] . "-" . $uCity["endip"];
        $status	    = 0;
        $time	    = time() - 86400;
        $rs_ip_segment_counts	= $this->_db->rows('select * from `logs_sys_cp` where `ip_segment` =:ip_segment and `status` =:status and `time` >=:time ;',array('ip_segment'=>$ip_segment,'status'=>$status,'time'=>$time));
        if ($rs_ip_segment_counts <= Const_Cp_IPs_Count)
        {
            $rs_ip_counts	= $this->_db->rows('select * from `logs_sys_cp` where `ip` =:ip and `status` =:status and `time` >=:time;',array('ip'=>$ip,'status'=>$status,'time'=>$time));
            if ($rs_ip_counts <= Const_Cp_IP_Count)
            {
                return array(true);
            }
            else
            {
                return array(false,'IP地址登录失败超过'.Const_Cp_IP_Count.'次');
            }
        }
        else
        {
            return array(false,'IP地址段登录失败超过'.Const_Cp_IPs_Count.'次');
        }
    }

    /**
     * 退出登录
     */
    public function login_out()
    {
        $this->set_account('');
        $this->set_account_id('');
        $this->set_cid(0);
        $this->set_hash('');
        $this->set_pass('');
        $this->set_type(0);
        $this->set_google('');
    }

    /**
     * Google身份验证
     * @param \ounun\Mysqli $db
     * @param int $cid
     * @param string $account
     */
    public function login_google($ext, $code)
    {
        if($ext && $ext['google'])
        {
            if($ext['google']['secret'])
            {
                if($ext['google']['is'])
                {
                    $ga     = new \plugins\google\GoogleAuthenticator();
                    return $ga->verifyCode($ext['google']['secret'],$code);
                }else
                {
                    return true;
                }
            }else
            {
                return false;
            }
        }else
        {
            return false;
        }
    }


    /**
     * 获得权限目录
     * @return multitype:
     */
    public function purview_data($type='')
    {
        $purview	= array();
        if (login_is())
        {
            if(''==$type)
            {
                $type   = $_SESSION[$GLOBALS['cp']['session_type']];
            }
            foreach ($GLOBALS['cp']['purview'] as $key1 => $data1)
            {
                $purview_sub	= array();
                foreach ($data1['sub'] as $key2 => $data2)
                {
                    if($data2['key'])
                    {
                        if(in_array($type, $data2['key']))
                        {
                            unset($data2['key']);
                            $purview_sub[$key2] = $data2;
                        }
                    }else
                    {
                        $purview_sub2	= array();
                        foreach ($data2['data'] as $key3 => $data3)
                        {
                            if($data3['key'] && in_array($type, $data3['key']) )
                            {
                                unset($data3['key']);
                                $purview_sub2[$key3] = $data3;
                            }
                        }
                        if ($purview_sub2)
                        {
                            $purview_sub[$key2] = array(
                                'name'	=> $data2['name'],
                                'data'	=> $purview_sub2,
                            );
                        }
                    }
                }
                //
                if($purview_sub)
                {
                    $purview[$key1] = array(
                        'name' 		=> $data1['name'],
                        'default'	=> $data1['default'],
                        'sub'		=> $purview_sub,
                    );
                }
            }
        }
        return $purview;
    }

    /**
     * 权限检测 多个
     * @param string $key
     * @return boolean
     */
    public function purview_check_multi($key)
    {
        $keys = explode('|',$key);
        if($keys && is_array($keys) )
        {
            foreach($keys as $v)
            {
                if(purview_check($v))
                {
                    return true;
                }
            }
        }
        return false;
    }
    /**
     * 权限检测
     * @param string $key
     * @return boolean
     */
    function purview_check($key)
    {
        $type  = $_SESSION[$GLOBALS['cp']['session_type']];
        if(!$type)
        {
            return false;
        }
        if('tree@root' == $key)
        {
            return in_array($type, $GLOBALS['cp']['purview_tree_root']);
        }
        elseif('tree@coop' == $key)
        {
            return in_array($type, $GLOBALS['cp']['purview_tree_coop']);
        }
        else
        {
            $key	= explode('@', $key);
            $data	= $GLOBALS['cp']['purview'][$key[0]];
            if($data && $data['sub'])
            {
                $sub	= 	$data['sub'][$key[1]];
                if($sub && $sub['key'])
                {
                    return in_array($type, $sub['key']);
                }
                foreach ($data['sub'] as $subs)
                {
                    if ($subs && is_array($subs) && !$subs['key'])
                    {
                        $sub	= 	$subs['data'][$key[1]];
                        if($sub && $sub['key'])
                        {
                            return in_array($type, $sub['key']);
                        }
                    }
                }
            }
        }
        return false;
    }


    /**
     * 返回UId
     * @param \ounun\Mysqli $db_game
     * @param array $args
     * @return number uid
     */
    public function user_info2uid(\ounun\Mysqli $db_game,$cid,$args)
    {
        $info_value	  = $args['info_value'];
        $info_field	  = $args['info_field'];
        $info_field   = in_array($info_field, array('uname','uid'))?$info_field:'uid';
        $rs       	  = $db_game->row("select `uid` from `user` where `{$info_field}` = :info_value and `cid` = :cid limit 1;",array('info_value'=>$info_value,'cid'=>$cid) );
        if($rs && $rs['uid'])
        {
            return (int)$rs['uid'];
        }
        return 0;
    }


    /*
     * 显示权限
     */
    public function qx_xs($type)
    {
        $purview_uuid = 0;
        $purview      = purview_data ( $type );
        $purview_keys = array_keys ( $purview );
        foreach ( $purview as $key1 => $data1 )
        {
            echo '<h4 style="color: blue;">' . $data1 ['name'] . '</h4>';
            foreach ( $data1 ['sub'] as $key2 => $data2 )
            {
                if ($data2 ['url'])
                {
                    echo $data2 ['name'] . ', ';
                } else
                {
                    $purview_uuid ++;
                    $i = 0;
                    foreach ( $data2 ['data'] as $key3 => $data3 )
                    {
                        $i ++;
                        if (0 == $i % 5)
                        {
                            echo '<br />';
                        }
                        if ($data3 ['name'])
                        {
                            echo $data3 ['name'] . ', ';
                        }
                    }
                }
            }
        }
    }
    /**
     * 得到用户组名
     */
    public function get_group_name($type=null)
    {
        if (!$type)
        {
            $type  = $this->get_type();
            if(!$type)
            {
                return false;
            }
        }
        return $this->_purview[$type];
    }

    /**
     * 得到管理员帐号
     */
    public function set_account($account)
    {
        $_SESSION[$this->_session_key] = $account;
    }
    public function get_account()
    {
        return $_SESSION[$this->_session_key];
    }

    /**
     * 得到管理员帐号ID
     */
    public function set_account_id($account_id)
    {
        $_SESSION[$this->_session_id] = $account_id;
    }
    public function get_account_id()
    {
        return $_SESSION[$this->_session_id];
    }

    /**
     * 得到管理员帐号ID
     */
    public function set_pass($pass)
    {
        $_SESSION[$this->_session_pass] = $pass;
    }
    public function get_pass()
    {
        return $_SESSION[$this->_session_pass];
    }

    /**
     * 帐号所属平台
     */
    public function set_cid($cid)
    {
        $_SESSION[$this->_session_cid] = $cid;
    }
    public function get_cid()
    {
        return (int)$_SESSION[$this->_session_cid];
    }

    /**
     * 帐号权限类型
     */
    public function set_type($type)
    {
        $_SESSION[$this->_session_type] = $type;
    }
    public function get_type()
    {
        return $_SESSION[$this->_session_type];
    }

    /**
     * 得到管理员帐号ID
     */
    public function set_hash($hash)
    {
        $_SESSION[$this->_session_hash] = $hash;
    }
    public function get_hash()
    {
        return $_SESSION[$this->_session_hash];
    }

    /**
     * 得到管理员帐号ID
     */
    function set_google($google_code)
    {
        $_SESSION[$this->_session_g] = $google_code;
    }
    function get_google()
    {
        return $_SESSION[$this->_session_g];
    }


    /**
     * cookie cid
     */
    function set_cookie_cid($cid,$is_login=false)
    {
        // 登录时设一下
        if($is_login)
        {
            setcookie('login_cid', $cid, time()+864000);
        }

        if(!$this->get_cid())
        {
            setcookie('cp_cid', $cid, time()+86400);
        }
    }
    function get_cookie_cid()
    {
        $cid  = $this->get_cid();
        if($cid)
        {
            return $cid;
        }
        return $_COOKIE['cp_cid'];
    }

    /**
     * cookie sid
     */
    public function set_cookie_sid($sid)
    {
        setcookie('cp_sid', $sid, time()+86400);
    }
    public function get_cookie_sid()
    {
        return (int)$_COOKIE['cp_sid'];
    }

    /**
     * 内部 设定key
     */
    private function __session_set()
    {
        // $this->_session_key = $session_key;
        $this->_session_id     = $this->_session_key.'_id';
        $this->_session_g      = $this->_session_key.'_g';
        $this->_session_pass   = $this->_session_key.'_pass';
        $this->_session_cid    = $this->_session_key.'_cid';
        $this->_session_type   = $this->_session_key.'_type';
        $this->_session_hash   = $this->_session_key.'_hash';
    }

    /**
     * 生成Hash
     * @param int $cid
     * @param string $account
     * @param string $password
     */
    private function __make_hash($cid, $account, $password)
    {
        return sha1($account.$cid. $this->_session_key . $password);
    }
}


