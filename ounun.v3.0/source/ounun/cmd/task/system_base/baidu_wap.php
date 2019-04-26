<?php

namespace ounun\cmd\task\system_base;

use ounun\cmd\task\libs\com_baidu;
use ounun\cmd\task\manage;
use ounun\cmd\task\struct;

abstract class baidu_wap extends _system
{
    /** @var string 分类 */
    public static $tag = 'system';
    /** @var string 子分类 */
    public static $tag_sub = 'baidu_wap';

    /** @var string 任务名称 */
    public static $name = '提交新网址 [baidu_wap]';
    /** @var string 定时 */
    public static $crontab = '{1-59} 10 * * *';
    /** @var int 最短间隔 */
    public static $interval = 86400;

    /**
     * 执行任务
     * @param array $input
     * @param int $mode
     * @param bool $is_pass_check
     */
    public function execute(array $input = [], int $mode = manage::Mode_Dateup, bool $is_pass_check = false)
    {
        try {
            $this->_logs_status = manage::Logs_Succeed;
            // $this->url_push_baidu_pc_mip();
            manage::logs_msg("Successful push baidu_wap",$this->_logs_status,__FILE__,__LINE__,time());
        } catch (\Exception $e) {
            $this->_logs_status = manage::Logs_Fail;
            manage::logs_msg($e->getMessage(),$this->_logs_status,__FILE__,__LINE__,time());
            manage::logs_msg("Fail push baidu_wap",$this->_logs_status,__FILE__,__LINE__,time());
        }
    }

    public function push_wap(array $urls)
    {
        $api = str_replace(['{$site}', '{$token}'], [$this->_url_root_wap, $this->_token_site], com_baidu::api_baidu_wap);
        return $this->_push($api, $urls);
    }

    /**
     * 定时  数据接口提交 mip
     * @param bool $is_today false :历史   true  :当天
     */
    public function do_push_wap($is_today = false)
    {
        $this->_push_step = com_baidu::max_push_step;
        do {
            $do = $this->_do_push(com_baidu::type_baidu_wap, $is_today);
        } while ($do);
    }
}
