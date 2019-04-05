<?php

namespace ounun\cmd\task;

use ounun\tool\crontab;

class struct
{
    /** @var crontab 定时对像 */
    protected $_crontab;

    /** @var int 任务自长id */
    public $task_id = 0;
    /** @var string task名称 */
    public $task_name = '';
    /** @var int 任务组id */
    public $group_id = 0;
    /** @var int 类型 0:指定日期时间 1:间隔时间 */
    public $type = 0;
    /** @var string 数据 0:[分 时 日 月 周] 1:[秒] */
    public $crontab = '';
    /** @var int 最小间隔 */
    public $interval_min = 59;
    /** @var int 执行次数 */
    public $count = 0;
    /** @var array 数据json ["任务tag","方法","参数1","参数2",...] */
    public $arguments = [];
    /** @var array 忽略结束时间 */
    public $time_ignore = 0;
    /** @var int 添加时间 */
    public $time_add = 0;
    /** @var int 开启时间 */
    public $time_begin = 0;
    /** @var int 结束时间 */
    public $time_end = 0;
    /** @var int 最后执行时间 */
    public $time_last = 0;

    /** @var array 扩展数据 */
    public $extend = [];

    /**
     * struct constructor.
     * @param array $data
     */
    public function __construct(array $data)
    {
        // 数据 0:[分 时 日 月 周] 1:[秒]
        if (isset($data['type']) && $data['type'] == manage::Type_Crontab && isset($data['crontab'])) {
            $this->crontab = (string)$data['crontab'];
            $this->_crontab = new crontab($this->crontab);
        } else {
            $this->crontab = (int)$data['crontab'];
            $this->_crontab = null;
        }
        /** @var int 任务自长id */
        isset($data['task_id']) && $this->task_id = (int)$data['task_id'];
        /** @var string task名称 */
        isset($data['task_name']) && $this->task_name = (string)$data['task_name'];
        /** @var int 任务组id */
        isset($data['group_id']) && $this->group_id = (int)$data['group_id'];
        /** @var int 类型 0:指定日期时间 1:间隔时间 */
        isset($data['type']) && $this->type = (int)$data['type'];
        /** @var string 数据 0:[分 时 日 月 周] 1:[秒] */
        // isset($data['crontab']) && $this->crontab  = (string)$data['crontab'];
        /** @var int 最小间隔 */
        isset($data['interval_min']) && $this->interval_min = (int)$data['interval_min'];
        /** @var int 执行次数 */
        isset($data['count']) && $this->count = (int)$data['count'];
        /** @var array 数据json ["任务tag","方法","参数1","参数2",...] */
        isset($data['arguments']) && $this->arguments = json_decode($data['arguments']);
        /** @var array 忽略结束时间 */
        isset($data['time_ignore']) && $this->time_ignore = (int)$data['time_ignore'];
        /** @var int 添加时间 */
        isset($data['time_add']) && $this->time_add = (int)$data['time_add'];
        /** @var int 开启时间 */
        isset($data['time_begin']) && $this->time_begin = (int)$data['time_begin'];
        /** @var int 结束时间 */
        isset($data['time_end']) && $this->time_end = (int)$data['time_end'];
        /** @var int 最后执行时间 */
        isset($data['time_last']) && $this->time_last = (int)$data['time_last'];

        /** @var array 扩展数据 */
        isset($data['extend']) && $this->extend = json_decode($data['extend']);
    }

    /**
     * 触发检查
     * @param int $time_curr 当前时间(秒)
     * @param bool $is_pass_check 是否不检查 直接过
     * @return array
     */
    public function check(int $time_curr = 0, bool $is_pass_check = false)
    {
        // 不检查 直接过
        if ($is_pass_check) {
            return succeed(true);
        }

        if (empty($time_curr)) {
            $time_curr = time();
        }

        // 当前任务 是否还没开始
        if ($this->time_begin && $time_curr < $this->time_begin) {
            return error('任务开时间为:' . date("Y-m-d H:i:s", $this->time_begin) . '，当前还没开始。');
        }

        // 当前任务 是否已过 结束时间
        if ($this->time_end && $this->time_end < $time_curr) {
            return error('任务开时间为:' . date("Y-m-d H:i:s", $this->time_begin) . '，当前还没开始。');
        }

        // 不能过与频繁执行
        if ($time_curr < $this->time_ignore) {
            return error('最近只能在:' . date("Y-m-d H:i:s", $this->time_begin) . '之后，不能过与频繁执行。');
        }

        if ($this->type == manage::Type_Crontab) {
            $rs = $this->_crontab->check($time_curr);
            if (error_is($rs)) {
                return $rs;
            }
            return $rs;
        } else {
            return succeed(true);
        }
    }

    /**
     * 更新 数据
     * @param int $time_curr 当前时间(秒)
     */
    public function update(int $time_curr = 0)
    {
        if (empty($time_curr)) {
            $time_curr = time();
        }

        $this->time_ignore = $time_curr + $this->interval_min;
        $this->time_last = $time_curr;
        $this->count += 1;
    }
}