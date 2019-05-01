<?php

namespace ounun\cmd\task\system_base;


use ounun\api_sdk\com_baidu;
use ounun\cmd\task\manage;

abstract class baidu_xzh_realtime extends _system
{
    /** @var string 分类 */
    public static $tag = 'system';
    /** @var string 子分类 */
    public static $tag_sub = 'baidu_xzh_realtime';

    /** @var string 任务名称 */
    public static $name = '提交熊掌号 [baidu_xzh_realtime]';
    /** @var string 定时 */
    public static $crontab = '{1-59} 11 * * *';
    /** @var int 最短间隔 */
    public static $interval = 86400;

    /**
     * 定时  数据接口提交 mip
     * @param bool $is_today
     */
    protected function _do(bool $is_today = false)
    {
        manage::logs_msg(__METHOD__ . "->_do", manage::Logs_Normal, __FILE__, __LINE__, time());

        $this->_push_step = com_baidu::max_push_xzh_doday;
        $this->_push_step_max = com_baidu::max_push_xzh_doday;
        do {
            $do = $this->_do_push(com_baidu::type_baidu_xzh_realtime, $is_today);
        } while ($do);
    }

    /**
     * @param array $urls_domain
     * @return mixed
     */
    public function _push_api(array $urls_domain)
    {
        $site_url = '';
        $api = com_baidu::replace(com_baidu::api_xzh_realtime, $this->_seo, $site_url);
        $rs = $this->_push_curl($api, $urls_domain);
        manage::logs_msg("\$rs:" . json_encode_unescaped($rs), manage::Logs_Normal, __FILE__, __LINE__, time());
        $success = (int)$rs['success_realtime'];
        $remain = (int)$rs['remain_realtime'];
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
