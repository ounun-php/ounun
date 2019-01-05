<?php
namespace ounun\cmd\task;


class base
{
    /** @var \ounun\mysqli   */
    protected $_db;
    /** @var manage */
    protected $_manage;
    /** @var \ounun\logs */
    protected $_logs;
    /** @var int 状态  0:正常(灰) 1:失败(红色) 6:突出(橙黄)  99:成功(绿色) */
    protected $_logs_state    = 0;
    /** @var \plugins\crontab 定时对像  */
    protected $_cron;
    /** @var bool 是否运行过 */
    protected $_is_run        = false;

    /** @var int 任务自长id */
    protected $_task_id       = 0;
    /** @var string task名称 */
    protected $_task_name     = '';
    /** @var string 分类 */
    protected $_tag           = '';
    /** @var string 子分类 */
    protected $_tag_sub       = '';


    /** @var int 类型 0:指定日期时间 1:间隔时间 */
    protected $_type          = 0;
    /** @var string 数据 0:[分 时 日 月 周] 1:[秒] */
    protected $_crontab       = '';
    /** @var array 数据(解析后数组) */
 // protected $_crontab_parse = [];
    /** @var int 最小间隔 */
    protected $_interval      = 59;
    /** @var array 数据json ["任务tag","方法","参数1","参数2",...]	 */
    protected $_args          = [];
    /** @var array 忽略结束时间 */
    protected $_ignore        = 0;
    /** @var int 添加时间 */
    protected $_time_add      = 0;
    /** @var int 开启时间 */
    protected $_time_begin    = 0;
    /** @var int 结束时间 */
    protected $_time_end      = 0;
    /** @var int 最后执行时间 */
    protected $_time_last     = 0;
    /** @var int 执行次数 */
    protected $_times         = 0;


    /**
     * base constructor.
     * @param manage $task_manage
     * @param string $tag
     * @param string $tag_sub
     */
    public function __construct(manage $task_manage,string $tag='',string $tag_sub ='')
    {
        $this->_manage        = $task_manage;
        $this->_db            = $task_manage->db;
        $this->_tag           = $tag;
        $this->_tag_sub       = $tag_sub;
    }

    /**
     * 初始化任务
     * @param int $task_id       任务自长id
     * @param string $task_name  task名称
     * @param int $type          类型 0:指定日期时间 1:间隔时间
     * @param string $crontab    数据 0:[分 时 日 月 周] 1:[秒]
     * @param int $interval      最小间隔
     * @param array $args        数据json ["任务tag","方法","参数1","参数2",...]
     * @param int $time_add      添加时间
     * @param int $time_begin    开启时间
     * @param int $time_end      结束时间
     * @param int $time_last     最后执行时间
     * @param int $times         执行次数
     */
    public function init(int $task_id,string $task_name,int $type,string $crontab,int $interval,
                         array $args,int $ignore,int $time_add,int $time_begin=0,int $time_end=0,int $time_last=0,int $times=0)
    {
        $this->_cron          = new \plugins\crontab($crontab);
        /** @var int 任务自长id */
        $this->_task_id       = $task_id;
        /** @var string task名称 */
        $this->_task_name     = $task_name;
        /** @var int 类型 0:指定日期时间 1:间隔时间 */
        $this->_type          = $type;
        /** @var string 数据 0:[分 时 日 月 周] 1:[秒] */
        $this->_crontab       = $crontab;
        /** @var array 数据(解析后数组) */
        // $this->_crontab_parse = [];
        /** @var int 最小间隔 */
        $this->_interval      = $interval;
        /** @var array 数据json ["任务tag","方法","参数1","参数2",...]	 */
        $this->_args          = $args;
        /** @var array 忽略结束时间 */
        $this->_ignore        = $ignore;
        /** @var int 添加时间 */
        $this->_time_add      = $time_add;
        /** @var int 开启时间 */
        $this->_time_begin    = $time_begin;
        /** @var int 结束时间 */
        $this->_time_end      = $time_end;
        /** @var int 最后执行时间 */
        $this->_time_last     = $time_last;
        /** @var int 执行次数 */
        $this->_times         = $times;
    }

    /** 检查 执行 */
    public function check(bool $is_check = false)
    {
        if($is_check)
        {
            return true;
        }
        
        $time_curr = time();
        if($time_curr < $this->_time_begin || $this->_time_end < $time_curr)
        {
            // echo "task_id:{$this->_task_id} type:{$this->_type} if( begin:{$this->_time_begin} <= curr:{$time_curr} && end:{$this->_time_end} >= curr:{$time_curr} )\n";
            return false;
        }
        if($time_curr < $this->_ignore)
        {
            // echo "task_id:{$this->_task_id} type:{$this->_type} if( curr:{$time_curr} < begin:{$this->_time_begin} )\n";
            return false;
        }
        if($this->_type == manage::type_crontab)
        {
            $this->_ignore  = $time_curr + 59;
            list($rs1,$rs2) = $this->_cron->check($time_curr);
            // print_r(['$rs1'=>$rs1,'$rs2'=>$rs2]);
            if(true !== $rs1 || true !== $rs2)
            {
                // echo "task_id:{$this->_task_id} rs1:{$rs1} rs2:{$rs2} \n";
                return false;
            }
            // echo "task_id:{$this->_task_id} type:{$this->_type} ----------------------------------------------------------------------------------------------\n";
            
            $this->_ignore     = $time_curr + $this->_interval;
            $this->_time_last  = $time_curr;
            $this->_times     += 1;
            return true;
        }else
        {
            // echo "task_id:{$this->_task_id} type:{$this->_type} ----------------------------------------------------------------------------------------------\n";
            
            $this->_ignore     = $time_curr + $this->_interval;
            $this->_time_last  = $time_curr;
            $this->_times     += 1;
            return true;
        }
    }

    /**
     * 执行任务
     * @param array $paras
     * @param bool  $is_check 
     */
    public function run(array $paras=[],bool $is_check = false)
    {
        if( !$this->check($is_check) ) { return ; }
        
        $this->logs_init($this->_tag,$this->_tag_sub);
        sleep(rand(4,10));
        
        $this->_logs_state = \ounun\logs::state_fail;
        $this->msg("Fail 没有任务");
    }

    /**
     * 执行完成
     * @param $run_time
     */
    public function done(float $run_time,string $table)
    {
        if( $this->_logs && $this->_is_run)
        {
            $bind = [
                'task_id'   => $this->_task_id,
                'ignore'    => $this->_ignore,
                'time_last' => $this->_time_last,
            ];
            $this->_is_run  = false;
            $this->_logs->exts(['times'=>$this->_times]);
            $this->_logs->write($this->_logs_state,$run_time,true);
            $this->_db->conn( " UPDATE {$table} SET `ignore` = :ignore ,`time_last` = :time_last ,`times` = `times` + 1 WHERE `task_id` = :task_id; ",$bind);
        }
    }

    /**
     * 执行单步
     * @param float $run_time
     * @param array $exts
     */
    public function done_step(float $run_time,array $exts = [])
    {
        if( $this->_logs)
        {
            $this->_logs->exts($exts);
            $this->_logs->write($this->_logs_state,$run_time,true);
        }
    }

    /**
     * 任务日志
     * @param int $task_id
     * @param string $tag
     * @param string $tag_sub
     * @param int $time_add
     */
    public function logs_init(string $tag = '', string $tag_sub = '', int $time_add = -1)
    {
        if(null ==  $this->_logs)
        {
            $this->_logs = new \ounun\logs($this->_manage->db);
        }
        $this->_is_run = true;
        $time_add      = $time_add == -1 ? time() : $time_add;
        $this->_logs->task($this->_task_id,$tag,$tag_sub,$time_add);
    }


    /**
     * 日志数据logs_data
     * @param int $state   状态  0:正常(灰) 1:失败(红色) 6:突出(橙黄)  99:成功(绿色)
     * @param int $time     时间
     * @param string $logs  内容
     */
    public function msg(string $msg,int $state=-1,int $time=-1)
    {
        $time  = $time  == -1 ? time() : $time;
        $state = $state == -1 ? $this->_logs_state : $state;
        $this->_logs->data($state,$time,$msg);
    }
}