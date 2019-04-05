<?php
namespace ounun\cmd\task\site_base;

use ounun\cmd\task\manage;
use ounun\cmd\task\struct;

abstract class baidu_xzh extends _site
{

    public function __construct(struct $task_struct, string $tag = '', string $tag_sub = '')
    {
        $this->_tag       = 'baidu_xzh';
        $this->_tag_sub   = '';

        parent::__construct($task_struct, $tag, $tag_sub);
    }
    /**
     * 执行任务
     * @param array $paras
     * @param bool  $is_check
     */
    public function execute(array $input = [], int $mode = manage::Mode_Dateup,bool $is_pass_check = false)
    {

        try {
            $this->_logs_state = manage::Logs_Succeed;
            $this->url_push_baidu_xzh();
            $this->msg("Successful push xzh");
        } catch (\Exception $e) {
            $this->_logs_state = manage::Logs_Fail;
            $this->msg($e->getMessage());
            $this->msg("Fail push xzh");
        }
    }

    /** */
    public function url_push_baidu_xzh()
    {
        $this->do_push_xzh_batch();
        $this->do_push_xzh_realtime();
    }
}
