<?php
namespace ounun\cmd\task;


class manage
{
    /** @var array 任务类型列表  */
    const type = [
        0 => '定时',
        1 => '循环',
    ];

    /** @var int 定时任务 */
    const type_crontab  = 0;

    /** @var int 循环任务 */
    const type_interval = 1;

    /** @var array 模式 0:采集全部   1:检查   2:更新 */
    const mode = [
        0 => '采集全部',
        1 => '检查',
        2 => '更新',
    ];

    /** @var int 0:采集全部 */
    const mode_all    = 0; // 0:采集全部   1:检查   2:更新
    /** @var int 1:检查 */
    const mode_check  = 1; // 1:检查
    /** @var int 2:更新 */
    const mode_dateup = 2; // 2:更新


    /** @var \ounun\mysqli   */
    public $db        = null;
    /** @var array<\task\base> 采集任务类 */
    public $tasks     = [];

    /** @var string 表名 */
    protected $_table = '';

    /**
     * _task constructor.
     * @param \ounun\mysqli $db
     */
    public function __construct(\ounun\mysqli $db)
    {
        $this->db   = $db;
    }

    /** 初始化 */
    public function init(int $task_id = 0,string $table = 'z_task')
    {
        $this->_table      = $table;
        $this->tasks       = [];
        $rss               = $this->db->data_array("SELECT * FROM `{$this->_table}`  ".($task_id?" where `task_id` = {$task_id} ":"").";");
        foreach ($rss as $v){
            $v['args']     = json_decode($v['args'],true);
            $this->tasks[$v['task_id']] = $this->task_factory($v['task_id'],$v['task_name'],$v['type'],$v['crontab'],$v['interval'],$v['args'],$v['ignore'],$v['time_add'],$v['time_begin'],$v['time_end'],$v['time_last'],$v['times']);
        }
    }
    /**
     * 单步执行
     * @param $args
     */
    public function run_step(int $task_id,array $paras = [])
    {
        if($task_id)
        {
            /** @var  base $task */
            $task     = $this->tasks[$task_id];
            if($task)
            {
                $run_time  = 0-microtime(true);
                $task->run($paras,true);
                $run_time += microtime(true);
                $task->done($run_time,$this->_table);
            }
        }
    }

    /** 执行全部 */
    public function run_all()
    {
        /** @var base $task */
        foreach ($this->tasks as $task)
        {
            $run_time  = 0-microtime(true);
            $task->run();
            $run_time += microtime(true);
            $task->done($run_time,$this->_table);
        }
    }

    /** 任务工厂 */
    protected function task_factory(int $task_id,string $task_name,int $type,string $crontab,int $interval, array $args,
                                    int $ignore, int $time_add,int $time_begin=0,int $time_end=0,int $time_last=0,int $times=0)
    {
        $cls   = $args['data'];
        if($cls)
        {
            $cls1   = explode('.',$cls,2)[0];
            $cls2   = "\\task\\{$cls1}";
            /** @var  base $task */
            $task   = new $cls2($this,$cls);
            if($task)
            {
                $task->init($task_id, $task_name, $type, $crontab, $interval,$args,$ignore, $time_add, $time_begin, $time_end, $time_last, $times);
                return $task;
            }
        }
        return null;
    }
}