<?php
/** 命名空间 */

namespace ounun\mvc\model;

/**
 * YgcmsUC
 * Ygcms阳光CMS 用户中心
 * @author 一平 dreamxyp@gmail.com
 */
class user
{
    /** 通信私钥   */
    protected $_key_private = '';
    /** cookie域名 */
    protected $_domain = '';

    /**
     * 设定 私钥&域名
     * @param $key_private
     * @param $domain
     */
    public function __construct($key_private, $domain)
    {
        $this->_key_private = $key_private;
        $this->_domain = $domain;
    }

    /**
     * 检查是否登录
     * @return int 返回UID >0:UID  <0:没有登录
     */
    public function check()
    {
        if ($_COOKIE['_']) {
            list($yg, , $openid_en, $time_en, $type, $hex) = explode('.', $_COOKIE['_']);
            if ($yg == 'yg' && $openid_en && $time_en && $hex) {
                $openid = \short_url_decode($openid_en);
                $time = \short_url_decode($time_en);
                $now_time = time();
                if ($time > $now_time) {
                    return -1; // 登录时间 比现在还晚
                }
                if ($type && $time + $type * 3600 < $now_time) {
                    return -2; // 登录超时
                }
                $str = $openid . $time . $type . $this->_key_private;
                $hex_old = substr(md5($str), 12, 6) . substr(sha1($str), 16, 10);
                if ($hex == $hex_old) {
                    return $openid;
                }
                return -3; // $hex
            }
            return -98; // 解析有问题
        }
        return -99; // Cookie不存在
    }

    /**
     * 登录
     * @param int $uid
     * @param int $type 0:不限  n:小时
     * @return string
     */
    public function login($openid, $oauth_type, $type = 0, $pre = 'yg')
    {
        $cstr = '';
        if ($openid) {
            $time = time();
            $str = $openid . $time . $type . $this->_key_private;
            $openid_en = \short_url_encode($openid);
            $time_en = \short_url_encode($time);
            $ot = $this->_oauth_types($oauth_type);
            $ot = implode('-', $ot);
            $cstr = "{$pre}.{$ot}.{$openid_en}.{$time_en}.{$type}." . substr(md5($str), 12, 6) . substr(sha1($str), 16, 10);
            setcookie('_', $cstr, $time * 2, '/', $this->_domain);
        }
        return $cstr;
    }

    /**
     * 退出
     */
    public function out()
    {
        setcookie('_', '', -1, '/', $this->_domain);
    }

    /**
     * @param  int $oauth_type
     * @param  string $_
     * @return array
     */
    private function _oauth_types($oauth_type = 0, $_ = '')
    {
        $_ = $_ ? $_ : $_COOKIE['_'];
        $rs = [];
        if ($_) {
            $ts = explode('.', $_COOKIE['_'])[1];
            if ($ts) {
                $ts = explode('-', $ts);
                if ($ts) {
                    foreach ($ts as $v) {
                        $v = (int)$v;
                        $rs[$v] = $v;
                    }
                }
            }
        }
        $oauth_type = (int)$oauth_type;
        if ($oauth_type) {
            $rs[$oauth_type] = $oauth_type;
        }
        if ($rs) {
            return array_values($rs);
        }
        return $rs;
    }
}