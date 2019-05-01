<?php

namespace ounun\cmd\task\system_base;

use ounun\api_sdk\com_baidu;
use ounun\cmd\task\manage;

abstract class baidu_mip extends _system
{
    /** @var string 分类 */
    public static $tag = 'system';
    /** @var string 子分类 */
    public static $tag_sub = 'baidu_mip';

    /** @var string 任务名称 */
    public static $name = '提交新网址 [baidu_mip]';
    /** @var string 定时 */
    public static $crontab = '{1-59} 10 * * *';
    /** @var int 最短间隔 */
    public static $interval = 86400;

    /**
     * 定时  数据接口提交 mip
     * @param bool $is_today
     */
    protected function _do(bool $is_today = false)
    {
        manage::logs_msg(__METHOD__ . "->_do", manage::Logs_Normal, __FILE__, __LINE__, time());

        $this->_push_step = com_baidu::max_push_step;
        $this->_push_step_max = com_baidu::max_push_step;
        do {
            $do = $this->_do_push(com_baidu::type_baidu_mip, $is_today);
        } while ($do);
    }

    /**
     * @param array $urls_domain
     * @return mixed
     */
    public function _push_api(array $urls_domain)
    {
        list($http, $space, $domain) = explode('/', $this->_url_root_mip);
        $site_url = "{$http}//{$domain}";
        $api = com_baidu::replace(com_baidu::api_baidu_mip, $this->_seo, $site_url);
        $rs = $this->_push_curl($api, $urls_domain);
        //
        manage::logs_msg("\$rs:" . json_encode_unescaped($rs), manage::Logs_Normal, __FILE__, __LINE__, time());
        $success = (int)$rs['success_mip'];
        $remain = (int)$rs['remain_mip'];
        return [$success, $remain];
    }

    /**
     * @param array $rs
     * @return array [$urls_path, $urls_domain]
     */
    protected function _urls(array $rs)
    {
        $root        = $this->_url_root_mip;
        // -------------------------------------
        $urls_path   = [];
        $urls_domain = [];
        foreach ($rs as $v) {
            $urls_path[] = $v['loc'];
            $urls_domain[] = $root . $v['loc'];
        }
        return [$urls_path, $urls_domain];
    }
}
