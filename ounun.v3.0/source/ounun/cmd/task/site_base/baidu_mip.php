<?php

namespace ounun\cmd\task\site_base;

use ounun\cmd\task\libs\com_baidu;
use ounun\cmd\task\manage;
use ounun\cmd\task\struct;

abstract class baidu_mip extends _site
{
    /**
     * baidu_mip constructor.
     * @param struct $task_struct
     * @param string $tag
     * @param string $tag_sub
     */
    public function __construct(struct $task_struct, string $tag = '', string $tag_sub = '')
    {
        $this->_tag = 'baidu_mip';
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
            $this->_logs_state = manage::Logs_Succeed;
            $this->url_push_baidu_pc_mip();
            $this->msg("Successful push baidu_mip");
        } catch (\Exception $e) {
            $this->_logs_state = manage::Logs_Fail;
            $this->msg($e->getMessage());
            $this->msg("Fail push baidu_mip");
        }
    }

    public function push_mip(array $urls)
    {
        $api = str_replace(['{$site}', '{$token}'], [$this->_url_root_mip, $this->_token_site], com_baidu::api_baidu_mip);
        return $this->_push($api, $urls);
    }


    /**
     * 定时  数据接口提交 mip
     * @param bool $is_today false :历史   true  :当天
     */
    public function do_push_mip($is_today = false)
    {
        $this->_push_step = com_baidu::max_push_step;
        do {
            $do = $this->_do_push(com_baidu::type_baidu_mip, $is_today);
        } while ($do);
    }

    /**
     * 定时  数据接口提交
     * @param bool $is_today false :历史   true  :当天
     */
    public function do_push($is_today = false)
    {
        $this->do_push_mip($is_today);
    }
}
