<?php

namespace ounun\cmd\task;

use ounun\cmd\console;
use ounun\mvc\model\admin\purview;

abstract class task_base
{
    /** @var string 分类 */
    public static $tag = '';
    /** @var string 子分类 */
    public static $tag_sub = '';

    /** @var string 任务名称 */
    public static $name = '';
    /** @var string 定时 */
    public static $crontab = '';
    /** @var int 最短间隔 */
    public static $interval = 59;
    /** @var string 类型 */
    public static $site_type = purview::app_type_admin;



    /** @var struct 任务数据结构 */
    protected $_task_struct;
    /** @var int 状态  0:正常(灰) 1:失败(红色) 6:突出(橙黄)  99:成功(绿色) */
    protected $_logs_status = manage::Logs_Normal;
    /** @var int 模式  0:采集全部  1:检查 2:更新   见 \task\manage::mode_XXX */
    protected $_mode = manage::Mode_Check;


    /** @var bool 是否运行过 */
    protected $_run_is = false;
    /** @var float 执行时间 */
    protected $_run_time = 0;
    /** @var int 执行次数 */
    protected $_run_count = 0;
    /** @var int 执行次数（失败） */
    protected $_run_count_fail = 0;
    /** @var int 执行次数（成功） */
    protected $_run_count_succeed = 0;
    /** @var int 执行状态 */
    protected $_run_status = manage::Status_Await;

    /**
     * task_base constructor.
     * @param struct $task_struct
     */
    public function __construct(struct $task_struct)
    {
        $this->_task_struct = $task_struct;
    }

    /** @return float 执行时间 */
    public function run_time_get()
    {
        return $this->_run_time;
    }

    /** @return float 执行次数 */
    public function run_count_get()
    {
        return $this->_run_count;
    }

    /** @return float 执行次数（失败） */
    public function run_count_fail_get()
    {
        return $this->_run_count_fail;
    }

    /** @return float 执行次数（成功） */
    public function run_count_succeed_get()
    {
        return $this->_run_count_succeed;
    }

    /** @return float 执行状态 */
    public function run_status_get()
    {
        return $this->_run_status;
    }

    /** @return struct */
    public function struct_get()
    {
        return $this->_task_struct;
    }

    /** @return string */
    public function tag_get()
    {
        return static::$tag;
    }

    /** @return string */
    public function tag_sub_get()
    {
        return static::$tag_sub;
    }

    /**
     * 执行任务
     * @param array $input
     * @param int $mode
     * @param bool $is_pass_check
     */
    public function execute_do(array $input = [], int $mode = manage::Mode_Dateup, bool $is_pass_check = false)
    {
        $this->_run_time = 0 - microtime(true);
        if (!$this->check($is_pass_check)) {
            return;
        }
        manage::logs_init();
        // execute
        $this->execute($input, $mode, $is_pass_check);
        // _run_time
        $this->_run_time += microtime(true);
        $this->done();
    }

    /**
     * 执行任务
     * @param array $input
     * @param int $mode
     * @param bool $is_pass_check
     */
    abstract public function execute(array $input = [], int $mode = manage::Mode_Dateup, bool $is_pass_check = false);

    /** 返回运行状态 */
    abstract public function status();

    /**
     * 检查 执行
     * @param bool $is_pass_check
     * @return array|bool
     */
    public function check(bool $is_pass_check = false)
    {
        if ($this->_task_struct && is_subclass_of($this->_task_struct, "ounun\\cmd\\task\\struct")) {
            $time_curr = time();
            $rs = $this->_task_struct->check($time_curr, $is_pass_check);
            if (error_is($rs)) {
                return $rs;
            }
            $this->_task_struct->update($time_curr);
            return true;
        }
        return error('task_struct数据有误');
    }

    /**
     * 执行完成
     */
    public function done()
    {
        if ($this->_run_is) {
            $bind = [
                'task_id' => $this->_task_struct->task_id,
                'time_ignore' => $this->_task_struct->time_ignore,
                'time_last' => $this->_task_struct->time_last,
            ];
            $this->_run_is = false;
            manage::logs_extend_set(['count' => $this->_task_struct->count]);
            manage::db_biz()->query(" UPDATE `" . manage::$table_task . "` SET `time_ignore` = :time_ignore ,`time_last` = :time_last ,`count` = `count` + 1 WHERE `task_id` = :task_id; ", $bind)->affected();
        }
        manage::logs_write($this->_logs_status, $this->run_time_get());
    }
}
