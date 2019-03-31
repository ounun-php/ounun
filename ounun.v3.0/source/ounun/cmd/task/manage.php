<?php
namespace ounun\cmd\task;


use ounun\mvc\controller\admin\adm;
use ounun\pdo;

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
    /** @var int 2:更新 */
    const Mode_Dateup = 1; // 2:更新
    /** @var int 1:检查 */
    const Mode_Check  = 99; // 1:检查
    /** @var array 模式 0:采集全部   1:检查   2:更新 */
    const Mode = [
        self::Mode_All => '采集全部',
        self::Mode_Check => '检查',
        self::Mode_Dateup => '更新',
    ];

    /** @var int 等待空置状态 */
    const Status_Await   = 1;
    /** @var int 工作中... */
    const Status_Runing  = 2;
    /** @var int 过载状态 */
    const Status_Full    = 99;
    /** @var array 1:空置(等待) 2:运行中... 99:满载(过载) */
    const Status = [
        self::Status_Await => '空置(等待)',
        self::Status_Runing => '运行中...',
        self::Status_Full => '满载(过载)',
    ];

    /** @var int 正常(灰) */
    const Logs_Normal       = 0;
    /** @var int 失败(红色) */
    const Logs_Fail         = 1;
    /** @var int 突出(橙黄) */
    const Logs_Warning      = 6;
    /** @var int 成功(绿色) */
    const Logs_Succeed      = 99;
    /** @var array 0:正常(灰) 1:失败(红色) 6:突出(橙黄)  99:成功(绿色) */
    const Logs = [
        self::Logs_Normal  => '正常(灰)',
        self::Logs_Fail    => '失败(红色)',
        self::Logs_Warning => '突出(橙黄)',
        self::Logs_Succeed => '成功(绿色)',
    ];

    /** @var self 单例 */
    protected static $_instance;
    /** @var \ounun\pdo   */
    protected static $_db;
    /** @var string 任务表名 */
    public static $table_task    = '';
    /** @var string 运行中的任务表名 */
    public static $table_process = '';
    /** @var string 日志的任务表名 */
    public static $table_logs    = '';

    /** @var logs */
    protected static $_logs;
    /** @var int 状态  0:正常(灰) 1:失败(红色) 6:突出(橙黄)  99:成功(绿色) */
    public static $logs_state    = 0;

    /**
     * 返回数据库连接对像
     * @param \ounun\pdo|null $db
     * @return manage
     */
    public static function instance(\ounun\pdo $db = null):self
    {
        if(empty(static::$_instance)) {
            if(empty($db)){
                $db = static::db();
            }
            static::$_instance = new static($db);
        }
        return static::$_instance;
    }

    /**
     * @return \ounun\pdo
     */
    public static function db()
    {
        if(empty( static::$_db )){
            static::$_db = pdo::instance('biz');
        }
        return static::$_db;
    }

    /**
     * @return logs
     */
    public static function logs()
    {
        if(empty(static::$_logs)) {
            static::$_logs = new logs(static::db());
        }
        return static::$_logs;
    }

    /**
     * 设定任务表与运行中的任务表
     * @param string $table_task      任务表
     * @param string $table_process   运行中的任务表
     * @param string $table_logs      日志的任务表
     */
    public static function table_set(string $table_task = 'sys_task',string $table_process = 'sys_task_process',string $table_logs = 'sys_logs_task')
    {
        if($table_task){
            self::$table_task = $table_task;
        }
        if($table_process){
            self::$table_process = $table_process;
        }
        if($table_logs){
            self::$table_logs = $table_logs;
        }
    }

    /**
     * 任务日志
     * @param int $task_id
     * @param string $tag
     * @param string $tag_sub
     * @param int $time
     * @param string $table
     * @param pdo|null $db
     */
    static public function logs_init(int $task_id, string $tag = '', string $tag_sub = '', int $time = 0,string $table = '',\ounun\pdo $db = null)
    {
        self::logs()->task($task_id,$tag,$tag_sub,$time,$table,$db);
    }

    /**
     * 日志数据logs_data
     * @param string $msg  内容
     * @param int $state   状态  0:正常(灰) 1:失败(红色) 6:突出(橙黄)  99:成功(绿色)
     * @param int $time    时间
     */
    static public function logs_msg(string $msg,int $state= self::Logs_Normal ,int $time = 0)
    {
        static::$_logs->data($state,$time,$msg);
    }

    /** @var array<task_id,task> 所有触发的任务Map */
    protected $_tasks     = [];
    /** @var array<task_id>      正在运行中的任务task_ids  */
    protected $_process   = [];

    /** @var int 任务ID */
    protected $_task_id = 0;
    /** @var struct 当前动行中的 */
    protected $_task;
    /** @var int 模式 */
    protected $_mode    = self::Mode_Dateup;
    /** @var int 运行状态 */
    protected $_status  = 0;

    /**
     * _task constructor.
     * @param \ounun\pdo $db
     */
    public function __construct(\ounun\pdo $db)
    {
        static::$_db  = $db;
    }

    /**
     * 返回运行状态
     * @return array
     */
    public function status()
    {

        return succeed([
            'task_id' => (empty($this->_task)?'':'"'.$this->_task->task_name.'"')."[task_id:{$this->_task_id}]",
            'mode'    => static::Mode[$this->_mode]."[mode:{$this->_mode}]",
            'status'  => static::Status[$this->_status]."[status:{$this->_status}]",
        ]);
    }

    /**
     * 执行任务
     * @param int $task_id  任务
     * @param int $mode     运行模式
     */
    public function execute(int $task_id,int $mode, array $input = [])
    {
        $this->_task_id = $task_id;
        $this->_mode    = $mode;

        if(empty($this->_status)){
            $this->status();
        }

        $is_pass_check  = $task_id ? true : false;
        if($this->_status < manage::Status_Full){
           $tasks = $this->tasks();
           if($tasks && is_array($tasks)) {
               /** @var task_base $task */
               foreach ($tasks as $task) {
                   $run_time   = 0-microtime(true);
                   $task->execute($input, $this->_mode,$is_pass_check);
                   $run_time += microtime(true);
               }
           }
        }
    }

    /**
     * php index.php zrun_cmd,crontab,5,595 adm
     * @param $mod
     */
    public function execute2()
    {
        print_r($mod);

        $time_sleep     = (int)$mod[1];
        $time_live      = (int)$mod[2];
        $time_sleep     = $time_sleep <= 1  ? 1  : $time_sleep;
        $time_live      = $time_live  <= 60 ? 60 : $time_live;

        $time_curr      = time();
        $time_past      = 0;
        $times          = 0;

        $task_manage    = new manage($this->_db);
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

    /**
     * 返回符合条件的任务列表
     * @return array
     */
    public function tasks()
    {
        if(empty($this->_tasks)) {
            $this->_tasks  = [];
            if(0 == $this->_task_id){
                $where = ' `task_id` = :task_id ';
                $param = ['task_id'=>$this->_task_id];
            }else {
                $where = ' `time_ignore` <= :time and `time_begin` <= :time and `time_end` >= :time  ';
                $param = ['time'=>time()];
            }
            $rs  = self::$_db->table(static::$table_task)->field('*')->where($where,$param)->column_all();
            foreach ($rs as $v){
                if($v && $v['task_id'] && $v['task_class']){
                    $cls    = '\\extend\\task\\'.$v['task_class'];
                    $struct = new struct($v);
                    /** @var task_base $task */
                    $task   = new $cls($struct,$v['tag'],$v['tag_sub']);
                    $this->_tasks[$v['task_id']] = $task;
                }
            }
        }
        return $this->_tasks;
    }

    /**
     * 正在运行中的任务task_ids
     * @return array
     */
    public function process()
    {
        if(empty($this->_process)){
            $this->_process = [];
            $rs    = self::$_db->table(static::$table_process)->field('*')->column_all();
            foreach ($rs as $v) {
                if($v && $v['task_id']){
                    $this->_process[$v['task_id']] = $v;
                }
            }
        }
        return $this->_process;
    }

    /**
     * @return int 返回模式
     */
    public function mode()
    {
        return $this->_mode;
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
        $task_manage->start($task_id,$paras);
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

        $task_manage    = new manage($this->_db);
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