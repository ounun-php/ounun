<?php

namespace ounun\mvc\model;


use ounun\config;
use ounun\tool\str;

class oauth_cookie
{
    /** @var int 默认超时时长 */
    const Timeout = 1800;
    /** @var int 登录校验 超时间 */
    const Overtime_Max = 600;

    /** @var string 通信私钥 */
    static protected $key_private = '';
    /** @var string cookie域名 */
    static protected $domain = '';
    /** @var string cookie前缀 */
    static protected $pre_key = '';
    /** @var string 登录cookie Key */
    static protected $cookie_key = '';
    /** @var int 登录校验 超时间 */
    static protected $overtime_max = 600;

    /**
     * 设定 私钥&域名&ookie前缀&登录cookie Key
     * @param string $key_private 通信私钥
     * @param string $domain 域名
     * @param string $pre_key cookie前缀
     * @param string $cookie_key 登录cookie Key
     * @param int $overtime_max 登录校验 超时间
     */
    static public function config_set(string $key_private = '', string $domain = '', string $pre_key = '', string $cookie_key = '', int $overtime_max = 0)
    {
        // 已设定 又没有设定新的想法 直接返回
        if (empty($key_private) && static::$key_private && static::$domain) {
            return;
        }
        // 设定
        $oauth_config = config::$global['oauth']?config::$global['oauth']:[];

        static::$key_private = $key_private ? $key_private : $oauth_config['login_key'];
        static::$domain = $domain ? $domain : $oauth_config['login_domain'];

        static::$pre_key = $pre_key ? $pre_key : ($oauth_config['pre_key']?$oauth_config['pre_key']:'a');
        static::$cookie_key = $cookie_key ? $cookie_key : ($oauth_config['cookie_key']?$oauth_config['cookie_key']:'_');
        static::$overtime_max = $overtime_max ? $overtime_max : ($oauth_config['overtime_max']?$oauth_config['overtime_max']:static::Overtime_Max);

        // 设定就有误 报错
        if (empty(static::$key_private) || empty(static::$domain) ) {
            trigger_error("Can't find config::\$global['oauth']", E_USER_ERROR);
        }
    }

    /**
     * 登录
     * @param int $uid
     * @param string $username
     * @param int $cid
     * @param array $oauth_type
     * @param int $timeouts 0:不限不超时  n:当时时间+n秒(n少于1800时为 1800)
     * @return bool
     */
    static public function login_set(int $uid, string $username = '', int $cid = 0, $oauth_type = [], $timeouts = 0)
    {
        // 设定
        static::config_set();
        // 登录
        if ($uid) {
            $time = time();
            if ($timeouts <= 0) {
                $timeouts = 0;
            } elseif ($timeouts < static::Timeout) {
                $timeouts = static::Timeout;
            }
            $oauth_type_en = ($oauth_type && is_array($oauth_type)) ? implode('-', $oauth_type) : '';
            $str = $uid . $oauth_type_en . $cid . $time . $timeouts . static::$key_private;
            $uid_en = short_url_encode($uid);
            $cid_en = short_url_encode($cid);
            $time_en = short_url_encode($time);
            $username_en = urlencode($username);
            $cookie_value = static::$pre_key . ".{$oauth_type_en}.{$uid_en}.{$cid_en}.{$time_en}.{$timeouts}." . substr(md5($str), 12, 6) . substr(sha1($str), 16, 10) . ".{$username_en}";
            setcookie(static::$cookie_key, $cookie_value, $time + 365 * 86400, '/', static::$domain);
        }
        return true;
    }

    /**
     * 检查是否登录
     * @return array 返回UID >0:UID  <0:没有登录
     */
    static public function login_parse()
    {
        // 设定
        static::config_set();
        // 执行
        if ($_COOKIE[static::$cookie_key]) {
            list($pre_key, $oauth_type_en, $uid_en, $cid_en, $time_en, $timeouts, $hex, $username_en) = explode('.', $_COOKIE[static::$cookie_key], 8);
            if ($pre_key == static::$pre_key && $uid_en && $time_en && $hex) {
                $oauth_type = $oauth_type_en ? explode('-', $oauth_type_en) : [];

                $uid = short_url_decode($uid_en);
                $cid = short_url_decode($cid_en);
                $time = short_url_decode($time_en);
                $username = urldecode($username_en);
                if (empty($uid)) {
                    return error('需要登录uid不能为0'); // 需要登录uid不能为0
                }
                $now_time = time();
                if ($time > $now_time) {
                    return error('登录时间有误'); // 登录时间 比现在还晚
                }
                if ($timeouts && $time + $timeouts < $now_time) {
                    return error('登录超时'); // 登录超时
                }
                $str = $uid . $oauth_type_en . $cid . $time . $timeouts . static::$key_private;
                $hex_old = substr(md5($str), 12, 6) . substr(sha1($str), 16, 10);
                if ($hex == $hex_old) {
                    return [$uid, $cid, $oauth_type, $timeouts, $username];
                }
                return error('登录校验有误(hex)');// $hex
            }
            return error('登录解析有误(explode)');    // 解析有问题
        }
        return error('登录记录不存在(cookie)');  // Cookie不存在
    }

    /**
     * 退出
     * @return bool
     */
    static public function logout()
    {
        // 设定
        static::config_set();
        // 执行
        setcookie(static::$cookie_key, '', -1, '/', static::$domain);
        return true;
    }

    /**
     * 是否登录 ，登录 返回uid 没登录 0
     * @return  int
     */
    static public function login_is()
    {
        $uid = static::uid_get();
        if (error_is($uid)) {
            return 0;
        }
        if (is_int($uid) && $uid > 0) {
            return $uid;
        }
        return 0;
    }

    /**
     * @param array $oauth_type_add_or_new 替换：新的数据    加/删:要新加的数据
     * @param bool $is_replace true 替换  false 加/删
     * @param array $oauth_type_del 替换：<可忽律>  加/删: 要删的数据
     * @return array|bool
     */
    static public function oauth_type_set(array $oauth_type_add_or_new = [], bool $is_replace = false, array $oauth_type_del = [])
    {
        $rs = static::login_parse();
        if (error_is($rs)) {
            return $rs;
        }
        list($uid, $cid, $oauth_type, $timeouts, $username) = $rs;

        // 加/删
        $rs2 = [];
        if (false == $is_replace) {
            // init
            if ($oauth_type && is_array($oauth_type)) {
                foreach ($oauth_type as $v) {
                    $v = (int)$v;
                    $rs2[$v] = $v;
                }
            }
            // 替换
            if ($oauth_type_add_or_new && is_array($oauth_type_add_or_new)) {
                foreach ($oauth_type_add_or_new as $v) {
                    $v = (int)$v;
                    $rs2[$v] = $v;
                }
            }
            // 删除
            if ($oauth_type_del && is_array($oauth_type_del)) {
                foreach ($oauth_type_del as $v) {
                    unset($rs2[$v]);
                }
            }
        } else {
            // 替换
            if ($oauth_type_add_or_new && is_array($oauth_type_add_or_new)) {
                foreach ($oauth_type_add_or_new as $v) {
                    $v = (int)$v;
                    $rs2[$v] = $v;
                }
            }
        }
        $oauth_type2 = array_values($rs2);

        // 写入cookie
        static::login_set($uid, $username, $cid, $oauth_type2, $timeouts);
        return true;
    }

    /**  获得权限组 */
    static public function oauth_type_get()
    {
        $rs = static::login_parse();
        if (error_is($rs)) {
            return $rs;
        }
        list($uid, $cid, $oauth_type, $timeouts, $username) = $rs;
        return $oauth_type;
    }

    /**
     * 设定 cid
     * @param int $cid_new
     * @return array | bool
     */
    static public function cid_set(int $cid_new)
    {
        $rs = static::login_parse();
        if (error_is($rs)) {
            return $rs;
        }
        list($uid, $cid, $oauth_type, $timeouts, $username) = $rs;

        // 写入cookie
        if ($cid != $cid_new) {
            static::login_set($uid, $username, $cid, $oauth_type, $timeouts);
        }
        return true;
    }

    /**
     * 获得 cid
     * @return array|int
     */
    static public function cid_get()
    {
        $rs = static::login_parse();
        if (error_is($rs)) {
            return $rs;
        }
        list($uid, $cid, $oauth_type, $timeouts, $username) = $rs;
        return $cid;
    }

    /**
     * 设定 username
     * @param string $username_new
     * @return array | string
     */
    static public function username_set(string $username_new)
    {
        $rs = static::login_parse();
        if (error_is($rs)) {
            return $rs;
        }
        list($uid, $cid, $oauth_type, $timeouts, $username) = $rs;

        // 写入cookie
        if ($username != $username_new) {
            static::login_set($uid, $username_new, $cid, $oauth_type, $timeouts);
        }
        return true;
    }

    /**
     * 获得 username
     * @return array|string
     */
    static public function username_get()
    {
        $rs = static::login_parse();
        if (error_is($rs)) {
            return $rs;
        }
        list($uid, $cid, $oauth_type, $timeouts, $username) = $rs;
        return $username;
    }

    /**
     * 获得 uid
     * @return array|int
     */
    static public function uid_get()
    {
        $rs = static::login_parse();
        if (error_is($rs)) {
            return $rs;
        }
        list($uid, $cid, $oauth_type, $timeouts, $username) = $rs;
        return $uid;
    }
}