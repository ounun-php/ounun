<?php
namespace ounun\cmd\task;


class manage
{
    /** @var int 定时任务 */
    const Type_Crontab  = 0;

    /** @var int 循环任务 */
    const Type_Interval = 1;

    /** @var array 任务类型列表  */
    const Type = [
        self::Type_Crontab  => '定时',
        self::Type_Interval => '循环',
    ];



    /** @var int 0:采集全部 */
    const Mode_All    = 0; // 0:采集全部   1:检查   2:更新
    /** @var int 1:检查 */
    const Mode_Check  = 1; // 1:检查
    /** @var int 2:更新 */
    const Mode_Dateup = 2; // 2:更新

    /** @var array 模式 0:采集全部   1:检查   2:更新 */
    const Mode = [
        self::Mode_All => '采集全部',
        self::Mode_Check => '检查',
        self::Mode_Dateup => '更新',
    ];


    /** @var \ounun\pdo   */
    public $db;
    /** @var array<\task\base> 采集任务类 */
    public $tasks     = [];

    /** @var string 表名 */
    protected $_table = '';

    /**
     * _task constructor.
     * @param \ounun\pdo $db
     */
    public function __construct(\ounun\pdo $db)
    {
        $this->db   = $db;
    }


    /**
     * 单步执行
     * @param $args
     */
    public function run_step(int $task_id,array $paras = [])
    {
        if($task_id)
        {
            /** @var  task_base $task */
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
        /** @var task_base $task */
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
            /** @var  task_base $task */
            $task   = new $cls2($this,$cls);
            if($task)
            {
                $task->init($task_id, $task_name, $type, $crontab, $interval,$args,$ignore, $time_add, $time_begin, $time_end, $time_last, $times);
                return $task;
            }
        }
        return null;
    }

    /** 初始化 */
    public function init(int $task_id = 0,string $table = 'z_task')
    {
        $this->_table      = $table;
        $this->tasks       = [];
        $rss               = $this->db->query("SELECT * FROM `{$this->_table}`  ".($task_id?" where `task_id` = {$task_id} ":"").";")->column_all();
        foreach ($rss as $v){
            $v['args']     = json_decode($v['args'],true);
            $this->tasks[$v['task_id']] = $this->task_factory($v['task_id'],$v['task_name'],$v['type'],$v['crontab'],$v['interval'],$v['args'],$v['ignore'],$v['time_add'],$v['time_begin'],$v['time_end'],$v['time_last'],$v['times']);
        }
    }


    /**
     * php index.php zrun_cmd,crontab_step,5 adm   任务ID
     * @param $mod
     */
    public function crontab_step($mod)
    {
        print_r($mod);

        $task_id      = $mod[1];
        $paras        = array_slice($mod,2);
        // print_r(['$paras'=>$paras]);

        $task_manage  = new manage($this->_db);
        $task_manage->init($task_id);
        $task_manage->run_step($task_id,$paras);
    }


    /**
     * php index.php zrun_cmd,crontab,5,595 adm
     * @param $mod
     */
    public function crontab($mod)
    {
        print_r($mod);

        $time_sleep     = (int)$mod[1];
        $time_live      = (int)$mod[2];
        $time_sleep     = $time_sleep <= 1  ? 1  : $time_sleep;
        $time_live      = $time_live  <= 60 ? 60 : $time_live;

        $time_curr      = time();
        $time_past      = 0;
        $times          = 0;

        $task_manage    = new manage($this->_db_zrun);
        $task_manage->init();
        do{
            $run_time   = 0-microtime(true);
            $task_manage->run_all();
            $run_time += microtime(true);
            echo "-------exec:".str_pad(round($run_time,4).'s', 8)."  ".
                "sleep:".str_pad($time_sleep, 5)." \$times:".str_pad($times, 5)."  ".
                "PastTime:".str_pad($time_past, 5)." \$live:".str_pad($time_live, 5)."\n";
            sleep($time_sleep);
            $times++;
            $time_past   = time() - $time_curr;
        }while($time_past <= $time_live);
    }
}