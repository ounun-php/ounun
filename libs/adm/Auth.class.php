<?php
/** 命名空间 */
namespace adm;

use ounun\Ret;

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
    private $_max_ips      = 20;
    private $_max_ip       = 5;

    /** table */
    private $_table_adm         = '';
    private $_table_logs_login  = '';
    private $_table_logs_act    = '';

    /**
     * 权限列表
     * @var array
     */
    private $_purview           = [];
    private $_purview_group     = [];
    private $_purview_root      = [10,20];
    private $_purview_coop      = [10,20,50];
    private $_purview_default   = 'info';

    /**
     * Mysqli 句柄
     * @var \ounun\Mysqli
     */
    private $_db;

    /** @var Auth */
    private static $_instance;
    public  static function instance()
    {
        if(!self::$_instance)
        {
            exit('error:'.__CLASS__.':'.__METHOD__.' not instance');
        }
        return self::$_instance;
    }

    /** Auth constructor. */
    public function __construct(\ounun\Mysqli $db,
                                $purview,
                                $purview_group,
                                $purview_default,
                                $purview_tree_coop,
                                $purview_tree_root,

                                $session_key,
                                $table_adm, $table_logs_login, $table_logs_act,
                                $max_ips = 20, $max_ip = 5)
    {
        $this->_db              = $db;
        // purview
        $this->_purview         = $purview;
        $this->_purview_group   = $purview_group;
        $this->_purview_default = $purview_default;
        $this->_purview_coop    = $purview_tree_coop;
        $this->_purview_root    = $purview_tree_root;
        // table
        $this->_table_adm       = $table_adm;
        $this->_table_logs_login= $table_logs_login;
        $this->_table_logs_act  = $table_logs_act;
        // ip
        $this->_max_ip         = $max_ip;
        $this->_max_ips        = $max_ips;
        // session key
        $this->_session_key    = $session_key;
        $this->_session_set();
    }

    /**
     * 是否登录
     * @return boolean
     */
    public function is():bool
    {
        $cid      = $this->get_cid();
        $account  = $this->get_account();
        $password = $this->get_pass();
        $hash     = $this->_make_hash($cid,$account,$password);
        if(!$hash)
        {
            return false;
        }
        if ($account && $this->get_hash() == $hash)
        {
            return true;
        }
        return false;
    }
    /**
     * 登录
     * $field : id,type,cid,account,password,note
     */
    function login(string $account,string $password,int $cid,string $code):Ret
    {
        $check      = $this->_check_ip($cid, $account);
        $account_id = 0;
        // 封帐号或IP
        if(!$check->ret)
        {
            $status  = 0;
            $this->_logs_login($status,$account_id,$cid,$account);
            return $check;
        }
        // 正常登录
        $bind    = ['account'=>$account,'cid'=>$cid];
        $rs 	 = $this->_db->row("select * from {$this->_table_adm} where `account` =:account and `cid` = :cid limit 1;",$bind);
        if($rs)
        {
            $ext       = $this->user_get_exts($rs,$rs['adm_id']);
            $is_google = $this->_check_google($ext, $code);
            //echo $this->_db->getSql();
            //print_r($rs);
            //exit();
            if($is_google || $ext['google']['is'] == false )
            {
                // echo "\$rs['password'] :{$rs['password']} == md5(\$password) :".md5($password)."<br />\n";
                // exit();
                if( $rs['password'] == md5($password) )
                {
                    // 清理一下
                    $this->out();
                    $this->set_cookie_cid($cid,true);
                    // 设定session
                    $hash = $this->_make_hash($cid,$account,$password);
                    $this->set_account($account);
                    $this->set_account_id($rs['adm_id']);
                    $this->set_cid($cid);
                    $this->set_hash($hash);
                    $this->set_pass($password);
                    $this->set_type($rs['type']);
                    $this->set_google($ext['google']['is']);
                    // 返回
                    $login_times = $rs['login_times'] + 1;
                    $login_last	 = time();
                    $bind	     = [ 'login_times'=>$login_times, 'login_last' =>$login_last ];
                    $this->_db->update($this->_table_adm, $bind,' `id`= ? ',$rs['id']);
                    $this->_logs_login(true,$account_id,$cid,$account);
                    return new Ret(true);
                }
                $this->_logs_login(false,$account_id,$cid,$account);
                return new Ret(false,0,'失败:帐号或密码有误');
            }
            $this->_logs_login(false,$account_id,$cid,$account);
            return new Ret(false,0,'失败:谷歌验证有误');
        }
        $this->_logs_login(false,$account_id,$cid,$account);
        return new Ret(false,0,'失败:帐号不存在');
    }

    /**
     * IP检查 是否锁定
     * @return Ret
     */
    private function _check_ip():Ret
    {
        $ip		    = \ounun\ip();
        $wry        = new \plugins\qqwry\QQWry('utf-8');
        $uCity      = $wry->getlocation($ip);
        $ip_segment	= $uCity["beginip"] . "-" . $uCity["endip"];

        $status	    = 0;
        $time	    = time() - 86400;
        $bind       = ['ip_segment'=>$ip_segment,'status'=>$status,'time'=>$time];
        $rs_ip_segment_counts	= $this->_db->rows("select * from {$this->_table_logs_login} where `ip_segment` =:ip_segment and `status` =:status and `time` >=:time ;",$bind);
        // echo $this->_db->getSql().'<br />';
        if ($rs_ip_segment_counts <= $this->_max_ips)
        {
            $bind           = ['ip'=>$ip,'status'=>$status,'time'=>$time];
            $rs_ip_counts	= $this->_db->rows("select * from {$this->_table_logs_login} where `ip` =:ip and `status` =:status and `time` >=:time;",$bind);
            // echo $this->_db->getSql().'<br />';
            // exit();
            if ($rs_ip_counts <= $this->_max_ip)
            {
                return new Ret(true);
            }
            else
            {
                return new Ret(false,0,'IP地址登录失败超过'.$this->_max_ip.'次');
            }
        }
        else
        {
            return new Ret(false,0,'IP地址段登录失败超过'.$this->_max_ips.'次');
        }
    }

    /**
     * Google身份验证
     * @param $ext
     * @param $code
     * @return bool
     */
    private function _check_google($ext, $code):bool
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
     * 记下登录日志
     * @param bool $status   状态 0:失败 1:成功
     * @param int $account_id
     * @param int $cid
     * @param string $account
     */
    private function _logs_login(bool $status,int $account_id, int $cid, string $account)
    {
        $ip		    = \ounun\ip();
        $wry        = new \plugins\qqwry\QQWry('utf-8');
        $uCity      = $wry->getlocation($ip);

        $time		= time();
        $ip_segment	= $uCity["beginip"] . "-" . $uCity["endip"];
        $address	= $uCity["country"];

        $status_d   = $status?0:1;

        $bind	    = [
            'time'      => $time,
            'status'    => $status_d,
            'adm_id'    => $account_id,
            'cid'       => $cid,
            'account'   => $account,
            'ip'        => $ip,
            'ip_segment'=> $ip_segment,
            'address'   => $address,
        ];
        $this->_db->insert($this->_table_logs_login, $bind);
    }

    /**
     * 操作日志
     * @param bool $status 状态 0:失败 1:成功
     * @param string $mod 模块
     * @param string $mod_sub 子模块
     * @param int $act 操作 0:普通 1:添加 2:修改 3:删除
     * @param array $exts 扩展数据
     */
    public function logs_act(bool $status, int $act, array $exts,string $url='')
    {
        if (!$url)
        {
            $url=$_SERVER['HTTP_REFERER'];
        }
        $ip    = \ounun\ip();
        $wry   = new \plugins\qqwry\QQWry('utf-8');
        $uCity = $wry->getlocation($ip);

        $account_id = $this->get_account_id();
        $cid        = $this->get_cid();
        $account    = $this->get_account();
        $time       = time();
        $address    = $uCity["country"];

        $exts = \ounun\json_encode($exts);

        $bind = [
            'time'    => $time,
            'status'  => $status,
            'adm_id'  => $account_id,
            'cid'     => $cid,
            'account' => $account,
            'ip'      => $ip,
            'mod'     => $GLOBALS['app'],
            'mod_sub' => $GLOBALS['mod'][0],
            'url'     => $url,
            'act'     => $act,
            'address' => $address,
            'exts'    => $exts,
        ];
        $this->_db->insert($this->_table_logs_act, $bind);
    }


    /**
     * 添加帐号
     * @return Ret
     */
    public function user_add(int $adm_type,int $adm_cid,string $adm_account,string $password,string $adm_tel,string $adm_note):Ret
    {
        // 看是否存在相同的帐号
        $rs             = $this->_db->row("SELECT `adm_id` FROM {$this->_table_adm} where `cid` = :cid  and `account` = :account limit 0,1;",['cid'=>$adm_cid,'account'=>$adm_account]);
        if($rs)
        {
            return new Ret(false,0,'提示：帐号"'.$adm_account.'"已存在!');
        }
        // 添加
        $adm_type_p     = $this->get_type();
        $adm_type       = $adm_type > $adm_type_p ? $adm_type : $adm_type_p;
        $bind	= [
            'cid'	        => $adm_cid,
            'account'       => $adm_account,
            'type'	        => $adm_type,
            'password'      => md5(md5($password)),
            'login_times'   => 0,
            'login_last'    => 0,
            'tel'           => $adm_tel,
            'note'	        => $adm_note,
            'exts'          => ''
        ];
        $adm_id = $this->_db->insert($this->_table_adm, $bind);
        // 记日志
        $this->logs_act($adm_id?1:0,'sys','adm',1,$bind);
        if($adm_id)
        {
            return new Ret(true,0,"成功:操作成功!");
        }
        return new Ret(false,0,'提示：系统忙稍后再试!');
    }

    /**
     * 更新帐号
     * @return Ret
     */
    public function user_modify():Ret
    {

    }
    /**
     * 帐号删除
     * @return Ret
     */
    public function user_del(int $adm_id):Ret
    {
        if($adm_id == $this->get_account_id())
        {
            return new Ret(false,0,'提示：不能删除自己[account_id]!');
        }
        $rs			 = $this->_db->row("SELECT `cid`,`account` FROM {$this->_table_adm} where `adm_id` = :adm_id limit 0,1;",['adm_id'=>$adm_id]);
        if($rs['cid'] == $this->get_cid() &&
           $rs['account'] == $this->get_account() )
        {
            return new Ret(false,0,'提示：不能删除自己[account]!');
        }
        $bind = ['adm_id'=>$adm_id,'cid'=>$rs['cid'], 'account'=>$rs['account'] ];
        $rs   = $this->_db->delete($this->_table_adm,'`adm_id`= :adm_id ',$bind);
        // 记日志
        $this->logs_act($rs?1:0,'sys','adm',3,$bind);
        if($rs)
        {
            return new Ret(true,0,"成功:操作成功!");
        }
        return new Ret(false,0,'提示：系统忙稍后再试!');
    }
    /**
     * 更改密码
     * @return Ret
     */
    public function user_modify_passwd($old_pwd,$new_pwd,$google_code):Ret
    {
        if(!$old_pwd)
        {
            return new Ret(false,0,'提示：请输入旧密码');
        }
        if(!$new_pwd)
        {
            return new Ret(false,0,'提示：请输入新密码');
        }
        $account_id	    = $this->get_account_id();
        $rs			    = $this->_db->row("SELECT `adm_id`,`password`,`exts` FROM {$this->_table_adm} where `adm_id` = ? ;",$account_id);
        $exts           = $this->user_get_exts($rs,$rs['adm_id']);
        $old_pwd_md5    = md5(md5($old_pwd));
        $new_pwd_md5	= md5(md5($new_pwd));


        if ($old_pwd_md5 != $rs['password'])
        {
            return new Ret(false,0,'提示：旧密码错误,请重新输入');
        }else if(!$this->_check_google($exts,$google_code))
        {
            return new Ret(false,0,'提示：请输入正确6位数谷歌(洋葱)验证');
        }
        else
        {
            //  跳回原来的页面
            $rs2 = $this->_db->update($this->_table_adm, ['password'=>$new_pwd_md5, ],' `adm_id`= ? ',$account_id);
            if($rs2)
            {
                return new Ret(true, 0,'成功：密码修改成功!');
            }else
            {
                return new Ret(false,0,'提示：系统忙,请稍后再试');
            }
        }
    }

    /**
     * 获得$exts
     * @return mixed
     */
    public function user_get_exts($rs=null,$account_id=0)
    {
        if(0 == $account_id)
        {
            $account_id	= $this->get_account_id();
        }
        if(!$rs)
        {
            $rs		    = $this->_db->row("SELECT `exts` FROM {$this->_table_adm} where `adm_id` = ? ;",$account_id);
            //echo $this->_db->getSql();
            //var_dump($rs);
        }
        $ext        = unserialize($rs['exts']);
        // var_dump($ext);
        if($ext && $ext['google'] && $ext['google']['secret'] && strlen($ext['google']['secret']) == 16)
        {
            // skip
            // $secret = $ext['google']['secret'];
        }else
        {
            $ga     = new \plugins\google\GoogleAuthenticator();
            $secret = $ga->createSecret();

            $google        = ['is'=>false,'secret'=>$secret];
            $ext['google'] = $google;
            $this->_db->update($this->_table_adm,['exts'=>serialize($ext)],' `adm_id` = ? ',$account_id);
            echo $this->_db->getSql();
        }
        return $ext;
    }

    /**
     * 设定 Google身份验证
     */
    public function user_set_exts_google($google_yn=true,$ext=null,$old_pwd='',$google_code=''):Ret
    {
        $account_id	= $this->get_account_id();
        if(!$ext && $old_pwd != '' && $google_code != '')
        {
            $rs		    = $this->_db->row("SELECT `adm_id`,`password`,`exts` FROM {$this->_table_adm} where `adm_id` = ? ;",$account_id);
            $ext        = $this->user_get_exts($rs,$rs['adm_id']);
            $oldpwd		= md5(md5($old_pwd));
            if ($oldpwd != $rs['password'])
            {
                return new Ret(false,0,'失败：登录密码有误!');
            }
            $ext['google']['is'] = true;
            if(!$this->_check_google($ext, $google_code) )
            {
                return new Ret(false,0,'失败：谷歌验证有误!');
            }
        }
        if(!$ext)
        {
            $ext        = $this->user_get_exts();
        }
        //
        if($google_yn)
        {
            $ext['google']['is'] = true;
            $this->set_google(true);
        }else
        {
            $ext['google']       = ['is'=>false,'secret'=>''];
            $this->set_google(false);
        }
        //
        $rs          = $this->_db->update($this->_table_adm,['exts'=>serialize($ext)],' `adm_id` = ? ',$account_id);
        if($rs)
        {
            return new Ret(true,0, '成功：操作成功!');
        }else
        {
            return new Ret(false,0,'提示:系统忙,请稍后再试');
        }
    }

//    /**
//     * 删除 Google身份验证
//     * @param $old_pwd
//     * @param $google_code
//     */
//    public function user_del_exts_google($old_pwd,$google_code)
//    {
//        return $this->user_set_exts_google(false,null,$old_pwd,$google_code);
//    }

    /**
     * 退出登录
     */
    public function out()
    {
        $this->set_account('');
        $this->set_account_id(0);
        $this->set_cid(0);
        $this->set_hash('');
        $this->set_pass('');
        $this->set_type(0);
        $this->set_google('');
    }

    /**
     * 获得Config
     * @return array
     */
    public function config_all():array
    {
        return [
            'adm_purview_default'   => $this->_purview_default,
            // 'adm_purview_group'     => $this->_purview_group,
            'adm_account'           => $this->get_account(),
            'adm_account_id'        => $this->get_account_id(),
            'adm_cid'               => $this->get_cid(),
            'adm_cookie_cid'        => $this->get_cookie_cid(),
            'adm_cookie_cid_login'  => $this->get_cookie_cid_login(),
            'adm_cookie_sid'        => $this->get_cookie_sid(),
            'adm_type'              => $this->get_type(),
            'adm_group_name'        => $this->get_group_name(),
        ];
    }
    /**
     * 获得权限目录
     * @param string $type
     * @return array
     */
    public function purview_data(int $type=0):array
    {
        $purview	= array();
        if ($this->is())
        {
            if(''==$type)
            {
                $type   = $this->get_type();
            }
            foreach ($this->_purview as $key1 => $data1)
            {
                $purview_sub	= array();
                if($data1['sub'])
                {
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
                                $purview_sub[$key2] = ['name' => $data2['name'], 'data'	=> $purview_sub2 ];
                            }
                        }
                    }
                }
                //
                if($purview_sub)
                {
                    $purview[$key1] = [
                        'name' 		=> $data1['name'],
                        'default'	=> $data1['default'],
                        'sub'		=> $purview_sub,
                    ];
                }
            }
        }
        return $purview;
    }

    /**
     * 权限检测 多个
     * @param string $key
     * @return bool
     */
    public function purview_check_multi(string $key):bool
    {
        $keys = explode('|',$key);
        if($keys && is_array($keys) )
        {
            foreach($keys as $v)
            {
                if($this->purview_check($v))
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
     * @return bool
     */
    public function purview_check(string $key):bool
    {
        $type  = $this->get_type();
        if(!$type)
        {
            return false;
        }
        if('tree@root' == $key)
        {
            return in_array($type, $this->_purview_root);
        }
        elseif('tree@coop' == $key)
        {
            return in_array($type, $this->_purview_coop);
        }
        else
        {
            $key	= explode('@', $key);
            $data	= $this->_purview[$key[0]];
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
    /*
     * 显示权限
     */
    public function purview_show(int $type)
    {
        $rs           = '';
        $uuid         = 0;
        $purview      = $this->purview_data ($type );
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
        return $rs;
    }
    /**
     * 得到用户组名
     * @param int|null $type
     * @return string
     */
    public function get_group_name(int $type=0):string
    {
        if (!$type)
        {
            $type  = $this->get_type();
            if(!$type)
            {
                return false;
            }
        }
        return $this->_purview_group[$type];
    }

    /**
     * @return array
     */
    public function get_group():array
    {
        return $this->_purview_group;
    }
    /**
     * 设定管理员帐号
     * @param string $account
     */
    public function set_account(string $account)
    {
        $_SESSION[$this->_session_key] = $account;
    }

    /**
     * 得到管理员帐号
     * @return string
     */
    public function get_account():string
    {
        return (string)$_SESSION[$this->_session_key];
    }

    /**
     * 得到管理员帐号ID
     * @param string $account_id
     */
    public function set_account_id(int $account_id)
    {
        $_SESSION[$this->_session_id] = $account_id;
    }

    /**
     * 得到管理员帐号ID
     * @return string
     */
    public function get_account_id():int
    {
        return (int)$_SESSION[$this->_session_id];
    }

    /**
     * @param string $pass
     */
    public function set_pass(string $pass)
    {
        $_SESSION[$this->_session_pass] = $pass;
    }

    /**
     * @return string
     */
    public function get_pass():string
    {
        return (string)$_SESSION[$this->_session_pass];
    }

    /**
     * 帐号所属平台
     * @param int $cid
     */
    public function set_cid(int $cid)
    {
        $_SESSION[$this->_session_cid] = $cid;
    }

    /**
     * 帐号所属平台
     * @return int
     */
    public function get_cid():int
    {
        return (int)$_SESSION[$this->_session_cid];
    }

    /**
     * 帐号权限类型
     * @param int $type
     */
    public function set_type(int $type)
    {
        $_SESSION[$this->_session_type] = $type;
    }

    /**
     * 帐号权限类型
     * @return int
     */
    public function get_type():int
    {
        return (int)$_SESSION[$this->_session_type];
    }

    /**
     * @param string $hash
     */
    public function set_hash(string $hash)
    {
        $_SESSION[$this->_session_hash] = $hash;
    }

    /**
     * @return string
     */
    public function get_hash():string
    {
        return $_SESSION[$this->_session_hash];
    }

    /**
     * @param string $google_yn
     */
    public function set_google(bool $google_yn)
    {
        $_SESSION[$this->_session_g] = $google_yn;
    }

    /**
     * @return string
     */
    public function get_google():bool
    {
        return (bool)$_SESSION[$this->_session_g];
    }

    /**
     * cookie cid
     * @param int $cid
     * @param bool $is_login
     */
    public function set_cookie_cid(int $cid,bool $is_login=false)
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

    /**
     * @return int
     */
    public function get_cookie_cid():int
    {
        $cid  = $this->get_cid();
        if($cid)
        {
            return (int)$cid;
        }
        return (int)$_COOKIE['cp_cid'];
    }

    /**
     * @return int
     */
    public function get_cookie_cid_login():int
    {
        return (int)$_COOKIE['login_cid'];
    }

    /**
     * cookie sid
     * @param int $sid
     */
    public function set_cookie_sid(int $hub_id)
    {
        setcookie('cp_sid', $hub_id, time()+86400);
    }

    /**
     * cookie sid
     * @return int
     */
    public function get_cookie_sid():int
    {
        return (int)$_COOKIE['cp_sid'];
    }


    /**
     * cookie game_id
     * @param int $sid
     */
    public function set_cookie_game_id(int $game_id)
    {
        setcookie('cp_game_id', $game_id, time()+86400);
    }

    /**
     * cookie game_id
     * @return int
     */
    public function get_cookie_game_id():int
    {
        return (int)$_COOKIE['cp_game_id'];
    }

    /**
     * 内部 设定key
     */
    private function _session_set()
    {
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
     * @return string
     */
    private function _make_hash(int $cid,string $account,string $password):string
    {
        if(!$account)
        {
            return '';
        }
        if(!$password)
        {
            return '';
        }
        return sha1($account.$cid. $this->_session_key . $password);
    }
}
