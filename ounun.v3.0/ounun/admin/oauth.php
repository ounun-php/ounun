<?php
/** 命名空间 */
namespace admin;

class oauth
{
    /** @var purview */
    public $purview;

    /** @var string  session key **/
    protected $_o_key  = '';
    /** @var \ounun\mysqli  Mysqli 句柄  */
    protected $_db;

    /** Auth constructor. */
    public function __construct(\ounun\mysqli $db,purview $purview ,string $session_key='adm')
    {
        $this->_db              = $db;
        $this->_o_key           = $session_key;
        $this->purview          = $purview;

        $this->purview->oauth   = $this;
    }

    /**
     * 是否登录
     * @return boolean
     */
    public function login_check():bool
    {
        $cid      =    (int)$this->session_get(purview::s_cid);
        $account  = (string)$this->session_get(purview::s_account);
        $password = (string)$this->session_get(purview::s_password);
        $hash     = $this->_make_hash($cid,$account,$password);
        if(!$hash)
        {
            return false;
        }
        if ($account && $this->session_get(purview::s_hash) == $hash)
        {
            return true;
        }
        return false;
    }
    
    /**
     * 登录
     * $field : id,type,cid,account,password,note
     */
    public function login(string $account, string $password, int $cid, string $code):\ret
    {
        $check      = $this->_check_ip($cid, $account);
        $account_id = 0;
        // 封帐号或IP
        if(!$check->ret)
        {
            $status  = 0;
            $this->logs_login($status,$account_id,$cid,$account);
            return $check;
        }
        // 正常登录
        $bind    = ['account'=>$account,'cid'=>$cid];
        $rs 	 = $this->_db->row("select * from {$this->purview->db_adm} where `account` =:account and `cid` = :cid limit 1;",$bind);

        if($rs)
        {
            $ext          = $this->user_get_exts($rs,$rs['adm_id']);
            $rs['exts2']  = $ext;
            $is_google    = $this->_check_google($ext, $code);
            //echo $this->_db->getSql();
            // print_r($rs);
            // exit();
            if($is_google || $ext['google']['is'] == false )
            {
                // echo "\$rs['password'] :{$rs['password']} == md5(\$password) :".md5($password)."<br />\n";
                // exit();
                if( $rs['password'] == md5($password) )
                {
                    // 清理一下
                    $this->logout();
                    // $this->set_cookie_cid($cid,true);
                    // 设定session
                    $hash = $this->_make_hash($cid,$account,$password);
                    $this->session_set(purview::s_account,  $account);
                    $this->session_set(purview::s_password, $password);
                    $this->session_set(purview::s_cid,      $cid);
                    $this->session_set(purview::s_hash,     $hash);
                    $this->session_set(purview::s_type,     $rs['type']);
                    $this->session_set(purview::s_id,       $rs['adm_id']);
                    $this->session_set(purview::s_google,   $ext['google']['is']);
                    // 返回
                    $login_times = $rs['login_times'] + 1;
                    $login_last	 = time();
                    $bind	     = [ 'login_times'=> $login_times, 'login_last' => $login_last ];
                    $this->_db->update($this->purview->db_adm, $bind,' `id`= ? ',$rs['id']);
                    $this->logs_login(true,$account_id,$cid,$account);

                    // print_r($_SESSION);
                    return new \ret(true);
                }
                $this->logs_login(false,$account_id,$cid,$account);
                return new \ret(false,0,'失败:帐号或密码有误');
            }
            $this->logs_login(false,$account_id,$cid,$account);
            return new \ret(false,0,'失败:谷歌验证有误');
        }
        $this->logs_login(false,$account_id,$cid,$account);
        return new \ret(false,0,'失败:帐号不存在');
    }


    /** 退出登录 */
    public function logout()
    {
        if($this->_o_key)
        {
            $key_len = strlen($this->_o_key);
            foreach ($_SESSION as $k => $v)
            {
                // echo "\$this->_o_key:{$k}-{$key_len}  ";
                if($this->_o_key == substr($k,0,$key_len))
                {
                    // echo "\$this->_o_key2:{$k}\n";
                    // $this->session_del($k);
                    $_SESSION[$k]='';
                    unset($_SESSION[$k]);
                }
            }
        }
    }

    /**
     * 记下登录日志
     * @param bool $status   状态 0:失败 1:成功
     * @param int $account_id
     * @param int $cid
     * @param string $account
     */
    public function logs_login(bool $status, int $account_id, int $cid, string $account)
    {
        $ip		    = ip();
        $wry        = new \plugins\qqwry\ip('utf-8');
        $uCity      = $wry->location($ip);

        $time		= time();
        $ip_segment	= $uCity["beginip"] . "-" . $uCity["endip"];
        $address	= $uCity["country"];

        $status_d   = $status?1:0;

        $bind	    = [
            'time'       => $time,
            'status'     => $status_d,
            'adm_id'     => $account_id,
            'cid'        => $cid,
            'account'    => $account,
            'ip'         => $ip,
            'ip_segment' => $ip_segment,
            'address'    => $address,
        ];
        $this->_db->insert($this->purview->db_logs_login, $bind);
    }

    /**
     * 操作日志
     * @param bool $status 状态 0:失败 1:成功
     * @param string $mod 模块
     * @param string $mod_sub 子模块
     * @param int $act 操作 0:普通 1:添加 2:修改 3:删除
     * @param array $exts 扩展数据
     */
    public function logs_act(bool $status, int $act, array $exts,string $url='',string $mod='',string $mod_sub='')
    {
        if (!$url)
        {
            $url=$_SERVER['HTTP_REFERER'];
        }
        $ip    = ip();
        $wry   = new \plugins\qqwry\ip('utf-8');
        $uCity = $wry->location($ip);

        $account_id = $this->session_get(purview::s_id);
        $cid        = $this->session_get(purview::s_cid);
        $account    = $this->session_get(purview::s_account);
        $time       = time();
        $address    = $uCity["country"];

        $exts = \json_encode_unescaped($exts);

        $bind = [
            'time'    => $time,
            'status'  => $status,
            'adm_id'  => $account_id,
            'cid'     => $cid,
            'account' => $account,
            'ip'      => $ip,
            'mod'     => $mod,
            'mod_sub' => $mod_sub,
            'url'     => $url,
            'act'     => $act,
            'address' => $address,
            'exts'    => $exts,
        ];
        $this->_db->insert($this->purview->db_logs_act, $bind);
    }


    /**
     * 添加帐号
     * @return \ret
     */
    public function user_add(int $adm_type,int $adm_cid,string $adm_account,string $password,string $adm_tel,string $adm_note):\ret
    {
        // 看是否存在相同的帐号
        $rs             = $this->_db->row("SELECT `adm_id` FROM {$this->purview->db_adm} where `cid` = :cid  and `account` = :account limit 0,1;",['cid'=>$adm_cid,'account'=>$adm_account]);
        if($rs)
        {
            return new \ret(false,0,'提示：帐号"'.$adm_account.'"已存在!');
        }
        // 添加
        $adm_type_p     = $this->session_get(purview::s_type);
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
        $adm_id = $this->_db->insert($this->purview->db_adm, $bind);
        // 记日志
        $this->logs_act($adm_id?1:0,1,$bind);
        if($adm_id)
        {
            return new \ret(true,0,"成功:操作成功!");
        }
        return new \ret(false,0,'提示：系统忙稍后再试!');
    }

    /**
     * 帐号删除
     * @return \ret
     */
    public function user_del(int $adm_id):\ret
    {
        if($adm_id == $this->session_get(purview::s_id))
        {
            return new \ret(false,0,'提示：不能删除自己[account_id]!');
        }
        $rs			 = $this->_db->row("SELECT `cid`,`account` FROM {$this->purview->db_adm} where `adm_id` = :adm_id limit 0,1;",['adm_id'=>$adm_id]);
        if($rs['cid'] == $this->session_get(purview::cp_cid) &&
           $rs['account'] == $this->session_get(purview::s_account) )
        {
            return new \ret(false,0,'提示：不能删除自己[account]!');
        }
        $bind = ['adm_id'=>$adm_id,'cid'=>$rs['cid'], 'account'=>$rs['account'] ];
        $rs   = $this->_db->delete($this->purview->db_adm,'`adm_id`= :adm_id ',$bind);
        // 记日志
        $this->logs_act($rs?1:0,3,$bind);
        if($rs)
        {
            return new \ret(true,0,"成功:操作成功!");
        }
        return new \ret(false,0,'提示：系统忙稍后再试!');
    }

    /**
     * 更改密码
     * @return \ret
     */
    public function user_modify_passwd($old_pwd,$new_pwd,$google_code):\ret
    {
        if(!$old_pwd)
        {
            return new \ret(false,0,'提示：请输入旧密码');
        }
        if(!$new_pwd)
        {
            return new \ret(false,0,'提示：请输入新密码');
        }
        $account_id	    = $this->session_get(purview::s_id);
        $rs			    = $this->_db->row("SELECT `adm_id`,`password`,`exts` FROM {$this->purview->db_adm} where `adm_id` = ? ;",$account_id);
        $exts           = $this->user_get_exts($rs,$rs['adm_id']);
        $old_pwd_md5    = md5(md5($old_pwd));
        $new_pwd_md5	= md5(md5($new_pwd));


        if ($old_pwd_md5 != $rs['password'])
        {
            return new \ret(false,0,'提示：旧密码错误,请重新输入');
        }else if(!$this->_check_google($exts,$google_code))
        {
            return new \ret(false,0,'提示：请输入正确6位数谷歌(洋葱)验证');
        }
        else
        {
            //  跳回原来的页面
            $rs2 = $this->_db->update($this->purview->db_adm, ['password'=>$new_pwd_md5, ],' `adm_id`= ? ',$account_id);
            if($rs2)
            {
                return new \ret(true, 0,'成功：密码修改成功!');
            }else
            {
                return new \ret(false,0,'提示：系统忙,请稍后再试');
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
            $account_id	= $this->session_get(purview::s_id);
        }
        if(!$rs)
        {
            $rs	    = $this->_db->row("SELECT `exts` FROM {$this->purview->db_adm} where `adm_id` = ? ;",$account_id);
        }
        $ext        = json_decode($rs['exts'],true);
        // print_r(['$ext'=>$ext,'$rs'=>$rs,'sql'=>$this->_db->sql()]);
        if($ext && $ext['google'] && $ext['google']['secret'] && strlen($ext['google']['secret']) == 16)
        {
            // skip
            // $secret = $ext['google']['secret'];
        }else
        {
            $ga     = new \plugins\google\auth_code();
            $secret = $ga->create_secret();

            $google        = ['is'=>false,'secret'=>$secret];
            $ext['google'] = $google;
            $this->_db->update($this->purview->db_adm,['exts'=>json_encode($ext)],' `adm_id` = ? ',$account_id);
            // echo $this->_db->getSql();
        }
        return $ext;
    }

    /**
     * 设定 Google身份验证
     */
    public function user_set_exts_google($google_yn=true,$ext=null,$old_pwd='',$google_code=''):\ret
    {
        $account_id	= $this->session_get(purview::s_id);
        if(!$ext && $old_pwd != '' && $google_code != '')
        {
            $rs		    = $this->_db->row("SELECT `adm_id`,`password`,`exts` FROM {$this->purview->db_adm} where `adm_id` = ? ;",$account_id);
            $ext        = $this->user_get_exts($rs,$rs['adm_id']);
            $oldpwd		= md5(md5($old_pwd));
            if ($oldpwd != $rs['password'])
            {
                return new \ret(false,0,'失败：登录密码有误!');
            }
            $ext['google']['is'] = true;
            if(!$this->_check_google($ext, $google_code) )
            {
                return new \ret(false,0,'失败：谷歌验证有误!');
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
            $this->session_set(purview::s_google,true);
        }else
        {
            $ext['google']       = ['is'=>false,'secret'=>''];
            $this->session_set(purview::s_google,false);
        }
        //
        $rs  = $this->_db->update($this->purview->db_adm,['exts'=>json_encode($ext)],' `adm_id` = ? ',$account_id);
        if($rs)
        {
            return new \ret(true, 0,'成功：操作成功!');
        }else
        {
            return new \ret(false,0,'提示:系统忙,请稍后再试');
        }
    }


    /** 内部 设定key */
    public function session_set($key, $val)
    {
        $_SESSION[$this->_o_key.$key]=$val;
    }

    /** 内部 获取key的值 */
    public function session_get($key)
    {
        return $_SESSION[$this->_o_key.$key];
    }

    /** 内部 删除key的值 */
    public function session_del($key)
    {
        // echo $this->_o_key.$key.":{$_SESSION[$this->_o_key.$key]}\n";
        $_SESSION[$this->_o_key.$key]='';
        unset($_SESSION[$this->_o_key.$key]);
        return true;
    }

    /**
     * cookie cid
     * @param int $cid
     * @param bool $is_login
     */
    public function cookie_set(string $key,string $val)
    {
        setcookie($key, $val, time()+864000);
    }

    /**
     * @param string $key
     * @return string
     */
    public function cookie_get(string $key)
    {
        return $_COOKIE[$key];
    }

    /**
     * @param string $key
     */
    public function cookie_del(string $key)
    {
        setcookie($key, '', 0);
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
        return sha1($account.$cid. $this->_o_key . $password);
    }

    /**
     * IP检查 是否锁定
     * @return \ret
     */
    protected function _check_ip($cid, $account):\ret
    {
        $ip		    = ip();
        $wry        = new \plugins\qqwry\ip('utf-8');
        $uCity      = $wry->location($ip);
        $ip_segment	= $uCity["beginip"] . "-" . $uCity["endip"];

        $status	    = 0;
        $time	    = time() - 86400;
        $bind       = ['ip_segment'=>$ip_segment,'status'=>$status,'time'=>$time];

        $rs_ip_segment_counts	= $this->_db->rows("select * from {$this->purview->db_logs_login} where `ip_segment` =:ip_segment and `status` =:status and `time` >=:time ;",$bind);
        // echo $this->_db->getSql().'<br />';
        if ($rs_ip_segment_counts <= $this->purview->max_ips)
        {
            $bind           = ['ip'=>$ip,'status'=>$status,'time'=>$time];
            $rs_ip_counts	= $this->_db->rows("select * from {$this->purview->db_logs_login} where `ip` =:ip and `status` =:status and `time` >=:time;",$bind);
            //
            // echo $this->_db->sql()."<br />\n";
            // exit();
            if ($rs_ip_counts <= $this->purview->max_ip)
            {
                return new \ret(true);
            }
            else
            {
                return new \ret(false,0,'IP地址登录失败超过'.$this->purview->max_ip.'次');
            }
        }
        else
        {
            return new \ret(false,0,'IP地址段登录失败超过'.$this->purview->max_ips.'次');
        }
    }

    /**
     * Google身份验证
     * @param $ext
     * @param $code
     * @return bool
     */
    protected function _check_google($ext, $code):bool
    {
        if($ext && $ext['google'])
        {
            if($ext['google']['secret'])
            {
                if($ext['google']['is'])
                {
                    $ga = new \plugins\google\auth_code();
                    return $ga->verify_code($ext['google']['secret'],$code,4);
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
}

