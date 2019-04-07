<?php

namespace ounun\cmd\task\system_base;

use ounun\cmd\task\libs\com_baidu;
use ounun\cmd\task\manage;
use ounun\cmd\task\struct;

abstract class baidu_xzh extends _system
{

    public static $name = '提交熊掌号 [baidu_xzh]';
    /** @var string 定时 */
    public static $crontab = '{1-59} 11 * * *';
    /** @var int 最短间隔 */
    public static $interval = 86400;

    /**
     * baidu_xzh constructor.
     * @param struct $task_struct
     * @param string $tag
     * @param string $tag_sub
     */
    public function __construct(struct $task_struct, string $tag = '', string $tag_sub = '')
    {
        $this->_tag = 'baidu_xzh';
        $this->_tag_sub = '';

        parent::__construct($task_struct, $tag, $tag_sub);
    }

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
            $this->url_push_baidu_xzh();
            manage::logs_msg("Successful push baidu_xzh",$this->_logs_status);
        } catch (\Exception $e) {
            $this->_logs_status = manage::Logs_Fail;
            manage::logs_msg($e->getMessage(),$this->_logs_status);
            manage::logs_msg("Fail push baidu_xzh",$this->_logs_status);
        }
    }

    public function push_xzh_batch(array $urls)
    {
        $api = str_replace(['{$appid}', '{$token}'], [$this->_appid_xzh, $this->_token_xzh], com_baidu::api_xzh_batch);
        return $this->_push($api, $urls);
    }

    public function push_xzh_realtime(array $urls)
    {
        $api = str_replace(['{$appid}', '{$token}'], [$this->_appid_xzh, $this->_token_xzh], com_baidu::api_xzh_realtime);
        return $this->_push($api, $urls);
    }

    /**
     * 定时  数据接口提交 mip
     * @param bool $is_today false :历史  true  :当天
     */
    public function do_push_xzh_realtime($is_today = false)
    {
        $this->_push_step = com_baidu::max_push_xzh_doday;
        do {
            $do = $this->_do_push(com_baidu::type_baidu_xzh_realtime, $is_today);
        } while ($do);
    }

    /**
     * 定时  数据接口提交 mip
     * @param bool $is_today false :历史   true  :当天
     */
    public function do_push_xzh_batch($is_today = false)
    {
        $this->_push_step = com_baidu::max_push_step;
        do {
            $do = $this->_do_push(com_baidu::type_baidu_xzh_batch, $is_today);
        } while ($do);
    }

    /** */
    public function url_push_baidu_xzh()
    {
        $this->do_push_xzh_batch();
        $this->do_push_xzh_realtime();
    }
}
