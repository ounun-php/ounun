<?php
/** 命名空间 */

namespace ounun\mvc\model\admin;

use ounun\mvc\controller\admin\adm;

class oauth
{
    /** @var self 单例 */
    protected static $_instance;

    /**
     * @param string $session_key
     * @return oauth 返回数据库连接对像
     */
    public static function instance(string $session_key = 'adm'): self
    {
        if (empty(static::$_instance)) {
            static::$_instance = new static($session_key);
        }
        return static::$_instance;
    }

    /** @var string  session key * */
    protected $_session_key = '';

    /**
     * oauth constructor.
     * @param string $session_key
     */
    public function __construct(string $session_key = 'adm')
    {
        $this->_session_key = $session_key;
    }

    /**
     * 是否登录
     * @return boolean
     */
    public function login_check(): bool
    {
        $cid = (int)$this->session_get(purview::session_cid);
        $account = (string)$this->session_get(purview::session_account);
        $password = (string)$this->session_get(purview::session_password);
        $hash = $this->_make_hash($cid, $account, $password);
        if (!$hash) {
            return false;
        }
        if ($account && $this->session_get(purview::session_hash) == $hash) {
            return true;
        }
        return false;
    }

    /**
     * 登录
     * @param string $account
     * @param string $password
     * @param int $cid
     * @param string $code
     * @return array
     */
    public function login(string $account, string $password, int $cid, string $code): array
    {
        $check = $this->_ip_check($cid, $account);
        $account_id = 0;
        // 封帐号或IP
        if (error_is($check)) {
            $status = 0;
            $this->logs_login($status, $account_id, $cid, $account);
            return $check;
        }
        // 正常登录
        $bind = ['account' => $account, 'cid' => $cid];
        $rs = adm::$db_adm
            ->table(adm::$purview->table_admin_user)
            ->field('*')
            ->where('`account` =:account and `cid` = :cid', $bind)
            ->limit(1)
            ->column_one();
        // $rs 	 = $this->_db->row("select * from {$this->purview->db_adm} where `account` =:account and `cid` = :cid limit 1;",$bind);
        if ($rs) {
            $ext = $this->user_extend_get($rs, $rs['adm_id']);
            $rs['exts2'] = $ext;
            $is_google = $this->_google_check($ext, $code);
            // echo adm::$db_adm->stmt()->queryString."<br />\n";
            // echo "\$rs['password'] :{$rs['password']} == md5(\$password) :".md5($password)."<br />\n";
            // print_r($rs);
            // exit();
            if ($is_google || $ext['google']['is'] == false) {
                if ($rs['password'] == md5($password)) {
                    // 清理一下
                    $this->logout();
                    // $this->set_cookie_cid($cid,true);
                    // 设定session
                    $hash = $this->_make_hash($cid, $account, $password);
                    $this->session_set(purview::session_account, $account);
                    $this->session_set(purview::session_password, $password);
                    $this->session_set(purview::session_cid, $cid);
                    $this->session_set(purview::session_hash, $hash);
                    $this->session_set(purview::session_type, $rs['type']);
                    $this->session_set(purview::session_id, $rs['adm_id']);
                    $this->session_set(purview::session_google, $ext['google']['is']);
                    // 返回
                    $login_times = $rs['login_times'] + 1;
                    $login_last = time();
                    $bind = ['login_times' => $login_times, 'login_last' => $login_last];
                    adm::$db_adm->table(adm::$purview->table_admin_user)->where('`adm_id`= :adm_id ', ['adm_id' => $rs['adm_id']])->update($bind);
                    // $this->_db->update($this->purview->db_adm, $bind,' `id`= ? ',$rs['id']);
                    $this->logs_login(true, $account_id, $cid, $account);
                    // print_r($_SESSION);
                    return succeed(true);
                }
                $this->logs_login(false, $account_id, $cid, $account);
                return error('失败:帐号或密码有误');
            }
            $this->logs_login(false, $account_id, $cid, $account);
            return error('失败:谷歌验证有误');
        }
        $this->logs_login(false, $account_id, $cid, $account);
        return error('失败:帐号不存在');
    }


    /** 退出登录 */
    public function logout()
    {
        if ($this->_session_key) {
            $key_len = strlen($this->_session_key);
            foreach ($_SESSION as $k => $v) {
                // echo "\$this->_o_key:{$k}-{$key_len}  ";
                if ($this->_session_key == substr($k, 0, $key_len)) {
                    // echo "\$this->_o_key2:{$k}\n";
                    // $this->session_del($k);
                    $_SESSION[$k] = '';
                    unset($_SESSION[$k]);
                }
            }
        }
    }

    /**
     * 记下登录日志
     * @param bool $status 状态 0:失败 1:成功
     * @param int $account_id
     * @param int $cid
     * @param string $account
     */
    public function logs_login(bool $status, int $account_id, int $cid, string $account)
    {
        $ip = ip();
        $wry = new \plugins\qqwry\ip('utf-8');
        $uCity = $wry->location($ip);

        $time = time();
        $ip_segment = $uCity["beginip"] . "-" . $uCity["endip"];
        $address = $uCity["country"];

        $status_d = $status ? 1 : 0;

        $bind = [
            'time' => $time,
            'status' => $status_d,
            'adm_id' => $account_id,
            'cid' => $cid,
            'account' => $account,
            'ip' => $ip,
            'ip_segment' => $ip_segment,
            'address' => $address,
        ];
        adm::$db_adm->table(adm::$purview->table_logs_login)->insert($bind);
    }

    /**
     * 操作日志
     * @param bool $status 状态 0:失败 1:成功
     * @param int $act 操作 0:普通 1:添加 2:修改 3:删除
     * @param array $exts 扩展数据
     * @param string $url
     * @param string $mod
     * @param string $mod_sub
     */
    public function logs_act(bool $status, int $act, array $exts, string $url = '', string $mod = '', string $mod_sub = '')
    {
        if (!$url) {
            $url = $_SERVER['HTTP_REFERER'];
        }
        $ip = ip();
        $wry = new \plugins\qqwry\ip('utf-8');
        $uCity = $wry->location($ip);

        $account_id = $this->session_get(purview::session_id);
        $cid = $this->session_get(purview::session_cid);
        $account = $this->session_get(purview::session_account);
        $time = time();
        $address = $uCity["country"];

        $exts = \json_encode_unescaped($exts);

        $bind = [
            'time' => $time,
            'status' => $status,
            'adm_id' => $account_id,
            'cid' => $cid,
            'account' => $account,
            'ip' => $ip,
            'mod' => $mod,
            'mod_sub' => $mod_sub,
            'url' => $url,
            'act' => $act,
            'address' => $address,
            'exts' => $exts,
        ];
        adm::$db_adm->table(adm::$purview->table_logs_act)->insert($bind);
    }

    /**
     * 添加帐号
     * @param int $adm_type
     * @param int $adm_cid
     * @param string $adm_account
     * @param string $password
     * @param string $adm_tel
     * @param string $adm_note
     * @return array
     */
    public function user_add(int $adm_type, int $adm_cid, string $adm_account, string $password, string $adm_tel, string $adm_note): array
    {
        // 看是否存在相同的帐号
        $rs = adm::$db_adm
            ->table(adm::$purview->table_admin_user)
            ->field('`adm_id`')
            ->limit(1)
            ->where('`cid` = :cid  and `account` = :account', ['i:cid' => $adm_cid, 's:account' => $adm_account])->column_one();
        if ($rs) {
            return error('提示：帐号"' . $adm_account . '"已存在!');
        }
        // 添加
        $adm_type_p = $this->session_get(purview::session_type);
        $adm_type = $adm_type > $adm_type_p ? $adm_type : $adm_type_p;
        $bind = [
            'cid' => $adm_cid,
            'account' => $adm_account,
            'type' => $adm_type,
            'password' => md5(md5($password)),
            'login_times' => 0,
            'login_last' => 0,
            'tel' => $adm_tel,
            'note' => $adm_note,
            'exts' => ''
        ];
        $adm_id = adm::$db_adm->table(adm::$purview->table_admin_user)->insert($bind);
        // 记日志
        $this->logs_act($adm_id ? 1 : 0, 1, $bind);
        if ($adm_id) {
            return error("成功:操作成功!");
        }
        return error('提示：系统忙稍后再试!');
    }

    /**
     * 帐号删除
     * @param int $adm_id
     * @return array
     */
    public function user_del(int $adm_id): array
    {
        if ($adm_id == $this->session_get(purview::session_id)) {
            return error('提示：不能删除自己[account_id]!');
        }
        $rs = adm::$db_adm->table(adm::$purview->table_admin_user)->field('`cid`,`account`,`type`')->limit(1)->where('`adm_id` = :adm_id', ['adm_id' => $adm_id])->column_one();
        //$rs		 = $this->_db->row("SELECT `cid`,`account` FROM {$this->purview->db_adm} where `adm_id` = :adm_id limit 0,1;",['adm_id'=>$adm_id]);
        if ($rs['cid'] == $this->session_get(purview::adm_cid) &&
            $rs['account'] == $this->session_get(purview::session_account)) {
            return error('提示：不能删除自己[account]!');
        }elseif (
            ($rs['type'] <= $this->session_get(purview::session_type)  &&  $rs['type'] > 10)  ||
             $rs['type'] < $this->session_get(purview::session_type)
        ){
            return error('提示：只能删除等级别低于自己的用户[type:'.$rs['type'].']!');
        }
        $bind = ['adm_id' => $adm_id, 'cid' => $rs['cid'], 'account' => $rs['account']];
        $rs = adm::$db_adm->table(adm::$purview->table_admin_user)->where('`adm_id`= :adm_id ', $bind)->delete(1);
        //$rs = $this->_db->delete($this->purview->db_adm,'`adm_id`= :adm_id ',$bind);
        // 记日志
        $this->logs_act($rs ? 1 : 0, 3, $bind);
        if ($rs) {
            return error("成功:操作成功!");
        }
        return error('提示：系统忙稍后再试!');
    }

    /**
     * 更改密码
     * @param string $old_pwd
     * @param string $new_pwd
     * @param string $google_code
     * @return array
     */
    public function user_passwd_modify(string $old_pwd, string $new_pwd, string $google_code): array
    {
        if (!$old_pwd) {
            return error('提示：请输入旧密码');
        }
        if (!$new_pwd) {
            return error('提示：请输入新密码');
        }
        $account_id = $this->session_get(purview::session_id);
        $rs = adm::$db_adm
            ->table(adm::$purview->table_admin_user)
            ->field('`adm_id`,`password`,`exts`')
            ->where('`adm_id` = :adm_id ', ['adm_id' => $account_id])
            ->column_one();
        // $rs		    = $this->_db->row("SELECT `adm_id`,`password`,`exts` FROM {$this->purview->db_adm} where `adm_id` = ? ;",$account_id);
        $exts = $this->user_extend_get($rs, $rs['adm_id']);
        $old_pwd_md5 = md5(md5($old_pwd));
        $new_pwd_md5 = md5(md5($new_pwd));

        if ($old_pwd_md5 != $rs['password']) {
            return error('提示：旧密码错误,请重新输入');
        } else if (!$this->_google_check($exts, $google_code)) {
            return error('提示：请输入正确6位数谷歌(洋葱)验证');
        } else {
            //  跳回原来的页面
            $rs2 = adm::$db_adm->table(adm::$purview->table_admin_user)->where(' `adm_id`= :adm_id ', ['adm_id' => $account_id])->update(['password' => $new_pwd_md5,]);
            // $rs2 = $this->_db->update($this->purview->db_adm, ['password'=>$new_pwd_md5, ],' `adm_id`= ? ',$account_id);
            if ($rs2) {
                return error('成功：密码修改成功!');
            } else {
                return error('提示：系统忙,请稍后再试');
            }
        }
    }

    /**
     * 获得$exts
     * @param array $rs
     * @param int $account_id
     * @return mixed
     */
    public function user_extend_get(array $rs = [], int $account_id = 0)
    {
        if (0 == $account_id) {
            $account_id = $this->session_get(purview::session_id);
        }
        if (!$rs) {
            $rs = adm::$db_adm->table(adm::$purview->table_admin_user)->field('`exts`')->where(' `adm_id`= :adm_id ', ['adm_id' => $account_id])->column_one();
            // $rs  = $this->_db->row("SELECT `exts` FROM {$this->purview->db_adm} where `adm_id` = ? ;",$account_id);
        }
        $ext = json_decode($rs['exts'], true);
        // print_r(['$ext'=>$ext,'$rs'=>$rs,'sql'=>$this->_db->sql()]);
        if ($ext && $ext['google'] && $ext['google']['secret'] && strlen($ext['google']['secret']) == 16) {
            // skip
            // $secret = $ext['google']['secret'];
        } else {
            $ga = new \plugins\google\auth_code();
            $secret = $ga->secret_create();

            $google = ['is' => false, 'secret' => $secret];
            $ext['google'] = $google;
            adm::$db_adm->table(adm::$purview->table_admin_user)->where(' `adm_id`= :adm_id ', ['adm_id' => $account_id])->update(['exts' => json_encode($ext)]);
            // $this->_db->update($this->purview->db_adm,['exts'=>json_encode($ext)],' `adm_id` = ? ',$account_id);
            // echo $this->_db->getSql();
        }
        return $ext;
    }

    /**
     * 设定 Google身份验证
     * @param bool $google_yn
     * @param array|null $ext
     * @param string $old_pwd
     * @param string $google_code
     * @return array
     */
    public function user_extend_google_set(bool $google_yn = true, array $ext = null, string $old_pwd = '', string $google_code = ''): array
    {
        $account_id = $this->session_get(purview::session_id);
        if (!$ext && $old_pwd != '' && $google_code != '') {
            $rs = adm::$db_adm->table(adm::$purview->table_admin_user)->field('`adm_id`,`password`,`exts`')->where(' `adm_id`= :adm_id ', ['adm_id' => $account_id])->column_one();
            // $rs		= $this->_db->row("SELECT `adm_id`,`password`,`exts` FROM {$this->purview->db_adm} where `adm_id` = ? ;",$account_id);
            $ext = $this->user_extend_get($rs, $rs['adm_id']);
            $oldpwd = md5(md5($old_pwd));
            if ($oldpwd != $rs['password']) {
                return error('失败：登录密码有误!');
            }
            $ext['google']['is'] = true;
            if (!$this->_google_check($ext, $google_code)) {
                return error('失败：谷歌验证有误!');
            }
        }
        if (!$ext) {
            $ext = $this->user_extend_get();
        }
        //
        if ($google_yn) {
            $ext['google']['is'] = true;
            $this->session_set(purview::session_google, true);
        } else {
            $ext['google'] = ['is' => false, 'secret' => ''];
            $this->session_set(purview::session_google, false);
        }
        //
        $rs = adm::$db_adm->table(adm::$purview->table_admin_user)->where(' `adm_id`= :adm_id ', ['adm_id' => $account_id])->update(['exts' => json_encode($ext)]);
        // $rs  = $this->_db->update($this->purview->db_adm,['exts'=>json_encode($ext)],' `adm_id` = ? ',$account_id);
        if ($rs) {
            return error('成功：操作成功!');
        } else {
            return error('提示:系统忙,请稍后再试');
        }
    }

    /**
     * 内部 设定key
     * @param string $key
     * @param mixed $val
     */
    public function session_set(string $key, $val)
    {
        $_SESSION[$this->_session_key . $key] = $val;
    }

    /**
     * 内部 获取key的值
     * @param $key
     * @return mixed
     */
    public function session_get($key)
    {
        return $_SESSION[$this->_session_key . $key];
    }

    /**
     * 内部 删除key的值
     * @param string $key
     * @return bool
     */
    public function session_del(string $key)
    {
        // echo $this->_o_key.$key.":{$_SESSION[$this->_o_key.$key]}\n";
        $_SESSION[$this->_session_key . $key] = '';
        unset($_SESSION[$this->_session_key . $key]);
        return true;
    }

    /**
     * cookie cid
     * @param string $key
     * @param string $val
     */
    public function cookie_set(string $key, string $val)
    {
        setcookie($key, $val, time() + 864000);
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
    private function _make_hash(int $cid, string $account, string $password): string
    {
        if (!$account) {
            return '';
        }
        if (!$password) {
            return '';
        }
        return sha1($account . $cid . $this->_session_key . $password);
    }

    /**
     * IP检查 是否锁定
     * @param int $cid
     * @param string $account
     * @return array
     */
    protected function _ip_check($cid = 0, $account = ''): array
    {
        $ip = ip();
        $wry = new \plugins\qqwry\ip('utf-8');
        $uCity = $wry->location($ip);
        $ip_segment = $uCity["beginip"] . "-" . $uCity["endip"];

        $status = 0;
        $time = time() - 86400;
        $bind = [':ip_segment' => $ip_segment, 'i:status' => $status, 'i:time' => $time];


        $rs_ip_segment_counts = adm::$db_adm->table(adm::$purview->table_logs_login)
            ->where('`ip_segment` = :ip_segment and `status` = :status and `time` >= :time ', $bind)
            ->count_value('`ip_segment`');

        if ($rs_ip_segment_counts <= adm::$purview->max_ips) {
            $bind = [':ip' => $ip, 'i:status' => $status, 'i:time' => $time];
            $rs_ip_counts = adm::$db_adm->table(adm::$purview->table_logs_login)
                ->where(' `ip` =:ip and `status` = :status and `time` >= :time ', $bind)
                ->count_value('`ip`');

            if ($rs_ip_counts <= adm::$purview->max_ip) {
                return succeed(true);
            } else {
                return error('IP地址登录失败超过' . adm::$purview->max_ip . '次');
            }
        } else {
            return error('IP地址段登录失败超过' . adm::$purview->max_ips . '次');
        }
    }

    /**
     * Google身份验证
     * @param mixed $ext
     * @param string $code
     * @return bool
     */
    protected function _google_check($ext, string $code): bool
    {
        if ($ext && $ext['google']) {
            if ($ext['google']['secret']) {
                if ($ext['google']['is']) {
                    $ga = new \plugins\google\auth_code();
                    return $ga->verify_code($ext['google']['secret'], $code, 4);
                } else {
                    return true;
                }
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}
