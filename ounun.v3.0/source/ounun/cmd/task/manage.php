<?php

namespace ounun\cmd\task;

use ounun\cmd\console;
use ounun\pdo;

class manage
{
    /** @var int 定时任务 */
    const Type_Crontab = 0;
    /** @var int 循环任务 */
    const Type_Interval = 1;
    /** @var array 任务类型列表 */
    const Type = [
        self::Type_Crontab => '定时',
        self::Type_Interval => '循环',
    ];

    /** @var int 0:采集全部 */
    const Mode_All = 0; // 0:采集全部   1:检查   2:更新
    /** @var int 2:更新 */
    const Mode_Dateup = 1; // 2:更新
    /** @var int 1:检查 */
    const Mode_Check = 99; // 1:检查
    /** @var array 模式 0:采集全部   1:检查   2:更新 */
    const Mode = [
        self::Mode_All => '采集全部(默认)',
        self::Mode_Check => '检查',
        self::Mode_Dateup => '更新',
    ];

    /** @var int 等待空置状态 */
    const Status_Await = 1;
    /** @var int 工作中... */
    const Status_Runing = 2;
    /** @var int 过载状态 */
    const Status_Full = 99;
    /** @var array 1:空置(等待) 2:运行中... 99:满载(过载) */
    const Status = [
        self::Status_Await => '空置(等待)',
        self::Status_Runing => '运行中...',
        self::Status_Full => '满载(过载)', 
    ];

    /** @var int 正常(灰) */
    const Logs_Normal = 0;
    /** @var int 失败(红色) */
    const Logs_Fail = 1;
    /** @var int 突出(橙黄) */
    const Logs_Warning = 6;
    /** @var int 成功(绿色) */
    const Logs_Succeed = 99;
    /** @var array 0:正常(灰) 1:失败(红色) 6:突出(橙黄)  99:成功(绿色) */
    const Logs = [
        self::Logs_Normal => '正常(灰)',
        self::Logs_Fail => '失败(红色)',
        self::Logs_Warning => '突出(橙黄)',
        self::Logs_Succeed => '成功(绿色)',
    ];

    /** @var string 任务表名 */
    public static $table_task = '';
    /** @var string 运行中的任务表名 */
    public static $table_process = '';

    /** @var self 单例 */
    protected static $_instance_manage;
    /** @var \ounun\pdo */
    protected static $_db_biz;
    /** @var \ounun\pdo */
    protected static $_db_caiji;
    /** @var \ounun\pdo */
    protected static $_db_site;

    /**
     * 返回数据库连接对像
     * @param pdo|null $db_biz
     * @param pdo|null $db_caiji
     * @param pdo|null $db_site
     * @return manage
     */
    public static function instance(\ounun\pdo $db_biz = null, \ounun\pdo $db_caiji = null, \ounun\pdo $db_site = null): self
    {
        if (empty(static::$_instance_manage)) {
            static::$_instance_manage = new static();
        }
        if ($db_task) {
            static::$_db_biz = $db_biz;
        }
        if ($db_caiji) {
            static::$_db_caiji = $db_caiji;
        }
        if ($db_site) {
            static::$_db_site = $db_site;
        }
        return static::$_instance_manage;
    }

    /**
     * @param string $db_tag
     * @param array $db_config
     * @return pdo 任务 数据库
     */
    public static function db_biz(string $db_tag = 'biz', array $db_config = [])
    {
        if (empty(static::$_db_biz)) {
            static::$_db_biz = pdo::instance($db_tag, $db_config);
        }
        return static::$_db_biz;
    }


    /**
     * @param string $db_tag
     * @param array $db_config
     * @return pdo 采集 数据库
     */
    public static function db_caiji(string $db_tag = 'caiji', array $db_config = [])
    {
        if (empty(static::$_db_caiji)) {
            static::$_db_caiji = pdo::instance($db_tag, $db_config);
        }
        return static::$_db_caiji;
    }

    /**
     * @param string $db_tag
     * @param array $db_config
     * @return pdo 站点 数据库
     */
    public static function db_site(string $db_tag = 'site', array $db_config = [])
    {
        if (empty(static::$_db_site)) {
            static::$_db_site = pdo::instance($db_tag, $db_config);
        }
        return static::$_db_site;
    }

    /**
     * 设定任务表与运行中的任务表
     * @param string $table_task 任务表
     * @param string $table_process 运行中的任务表
     * @param string $table_logs 日志的任务表
     */
    public static function table_set(string $table_task = '`sys_task`', string $table_process = '`sys_task_process`', string $logs_table_task = '`sys_logs_task`', string $logs_table_task_details = '`sys_logs_task_details`')
    {
        if ($table_task) {
            self::$table_task = $table_task;
        }
        if ($table_process) {
            self::$table_process = $table_process;
        }
        static::logs_table_set($logs_table_task,$logs_table_task_details);
    }

    /**
     * @return int 返回模式
     */
    public function mode_get()
    {
        return $this->_mode;
    }

    /**
     * 当前执行中的任务
     * @return task_base
     */
    public function task_curr_get()
    {
        return $this->_task_curr;
    }

    /** @var string 表名 */
    static protected $_logs_table_task = '';
    /** @var string 表名 */
    static protected $_logs_table_task_details = '';

    /** @var array 日志数据logs_data */
    static protected $_logs_data = [];
    /** @var array 日志数据(重要) */
    static protected $_logs_data_important = [];
    /** @var int 添加时间 */
    static protected $_logs_time_add = 0;
    /** @var array 任务参数paras */
    static protected $_logs_extend = [];
    /** @var int 状态  0:正常(灰) 1:失败(红色) 6:突出(橙黄)  99:成功(绿色) */
    static protected $_logs_status = self::Logs_Normal;

    /**
     * 任务日志
     * @param int $task_id
     * @param string $tag
     * @param string $tag_sub
     * @param int $time
     * @param string $table
     * @param pdo|null $db
     */
    static public function logs_init(int $time_add  = 0)
    {
        static::$_logs_data = [];
        static::$_logs_data_important = [];
        static::$_logs_time_add = $time_add == 0 ? time() : $time_add;
        static::$_logs_extend = [];
        static::$_logs_status = manage::Logs_Normal;
    }

    /**
     * 任务参数paras/扩展json
     * @param array $extend
     */
    static public function logs_extend_set(array $extend = [])
    {
        static::$_logs_extend = $extend;
    }

    /**
     * @param string $table_logs_task
     * @param string $table_logs_task_details
     */
    static public function logs_table_set(string $logs_table_task = '', string $logs_table_task_details = '')
    {
        if ($logs_table_task) {
            static::$_logs_table_task = $logs_table_task;
        }
        if ($logs_table_task_details) {
            static::$_logs_table_task_details = $logs_table_task_details;
        }
    }

    /**
     * 日志数据logs_data
     * @param string $msg 内容
     * @param int $status 状态  0:正常(灰) 1:失败(红色) 6:突出(橙黄)  99:成功(绿色)
     * @param int $time 时间
     */
    static public function logs_msg(string $msg, int $status = self::Logs_Normal, int $time = 0)
    {
        $time = $time == 0 ? time() : $time;
        /**  状态  时间 内容  */
        $data = ['s' => $status, 't' => $time, 'l' => $msg];
        if(static::Logs_Fail == $status || static::Logs_Warning == $status || static::Logs_Succeed == $status ){
            static::$_logs_data[] = $data;
        }
        static::$_logs_data_important[] = $data;
    }

    /**
     * 写入日志
     * @param int $status
     * @param float $run_time
     * @param bool $over_clean 写完是否清理logs数据
     */
    static public function logs_write(int $status, float $run_time, bool $over_clean = true)
    {
        $m       = static::instance();
        if($m && $m->task_curr_get() && $m->task_curr_get()->struct_get() ){
            $task_id   = $m->task_curr_get()->struct_get()->task_id;
            $task_curr = $m->task_curr_get();
            if ( $task_id && static::$_logs_data ) {
                // $this->_state  = $state;
                $bind = [
                    'task_id' => $task_id,
                    'tag' => $task_curr->tag_get(),
                    'tag_sub' => $task_curr->tag_sub_get(),
                    'state' => $status,
                    'data' => json_encode(static::$_logs_data_important, JSON_UNESCAPED_UNICODE),
                    'time_add' => static::$_logs_time_add,
                    'time_end' => time(),
                    'time_run' => $run_time,
                    'extend' => json_encode(static::$_logs_extend, JSON_UNESCAPED_UNICODE),
                ];
                $id = 0;
                $db = manage::db_biz();
                if($db){
                    $id = $db->table(static::$_logs_table_task)->insert($bind);
                    if($id){
                        $bind_details = [];
                        $db->table(static::$_logs_table_task_details)->insert($bind_details);
                    }
                }
                // $id
                if ($id && $over_clean) {
                    static::logs_init(0);
                }
                // echo $this->_db->sql()."\n";
            }
        }
    }

    /**
     * DROP TABLE IF EXISTS `z_task_logs`;
     * CREATE TABLE IF NOT EXISTS `z_task_logs` (
     * `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '自增长ID',
     * `task_id` bigint(20) UNSIGNED NOT NULL DEFAULT 0 COMMENT '任务ID',
     * `tag` varchar(128) NOT NULL DEFAULT '' COMMENT '分类/标识',
     * `tag_sub` varchar(128) NOT NULL DEFAULT '' COMMENT '子分类',
     * `state` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '状态',
     * `data` text NOT NULL COMMENT '数据json [{...},{...}]',
     * `time_add` bigint(20) UNSIGNED NOT NULL DEFAULT 0 COMMENT '添加时间',
     * `time_end` bigint(20) UNSIGNED NOT NULL DEFAULT 0 COMMENT '完成时间',
     * `exts` text NOT NULL COMMENT '任务参数paras/扩展json',
     * PRIMARY KEY (`id`),
     * KEY `state` (`state`),
     * KEY `cls` (`tag`,`tag_sub`)
     * ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='任务列表' ROW_FORMAT=COMPACT;
     */

    /** @var array<task_id,task> 所有触发的任务Map */
    protected $_tasks = [];
    /** @var array<task_id>      正在运行中的任务task_ids */
    protected $_process = [];
    /** @var task_base 当前执行中的任务 */
    protected $_task_curr;

    /** @var int 任务ID */
    protected $_task_id = 0;
    /** @var int 模式 */
    protected $_mode = self::Mode_Dateup;
    /** @var int 运行状态 */
    protected $_status = 0;
    /** @var int 间隔(秒,默认5秒) */
    protected $_time_sleep = 5;
    /** @var int 寿命(秒,默认300秒) */
    protected $_time_live = 59;
    /** @var int 当前时间 */
    protected $_time_curr = 0;
    /** @var int 过去的时间 */
    protected $_time_past = 0;
    /** @var int 执行次数 */
    protected $_run_count = 0;

    /**  */
    public function init()
    {
        if (empty($this->_status)) {
            $this->status();
        }
    }

    /**
     * 返回运行状态
     * @return array
     */
    public function status()
    {
        return succeed([
            'task_id' => (empty($this->_task) ? '' : '"' . $this->_task->task_name . '"') . "[task_id:{$this->_task_id}]",
            'mode' => static::Mode[$this->_mode] . "[mode:{$this->_mode}]",
            'status' => static::Status[$this->_status] . "[status:{$this->_status}]",
        ]);
    }

    /**
     * 执行任务
     * @param int $task_id 任务
     * @param int $mode 运行模式
     */
    public function execute(int $task_id, int $mode, int $time_sleep, int $time_live, array $input = [])
    {
        $time_sleep = $time_sleep <= 5 ? 5 : $time_sleep;
        $time_live = $time_live <= 60 ? 60 : $time_live;
        $is_pass_check = $task_id ? true : false;

        $this->_task_id = $task_id;
        $this->_mode = $mode;
        $this->_time_sleep = $time_sleep;
        $this->_time_live = $time_live;
        $this->_time_curr = time();
        $this->_time_past = 0;
        $this->_run_count = 0;

        $this->init();
        do {
            $tasks = $this->tasks();
            console::echo("Start          \$tasks_count:" . str_pad(count($tasks), 5) .
                "\$sleep:" . str_pad($time_sleep, 5) . " \$count:" . str_pad($this->_run_count, 5) . "  " .
                "\$past:" . str_pad($this->_time_past, 5) . " \$live:" . str_pad($time_live, 5) . ' ----------------- ', console::Color_Purple);
            /** @var task_base $task */
            foreach ($tasks as $task) {
                // var_dump(['$task'=>$task]);
                if ($task && is_subclass_of($task, "ounun\\cmd\\task\\task_base")) {
                    $this->_task_curr = $task;
                    console::echo("\$task_id:" . str_pad($task->struct_get()->task_id, 5) . " \$run_time:" . str_pad(round($task->run_time_get(), 4) . 's', 8), console::Color_Brown);
                    $this->_task_curr->execute_do($input, $this->_mode, $is_pass_check);
                }
            }
            $this->_run_count++;
            sleep($time_sleep);
            $this->_time_past = time() - $this->_time_curr;
            $this->status();
        } while ($this->_time_past < $this->_time_live);
    }

    /**
     * 返回符合条件的任务列表
     * @return array
     */
    public function tasks()
    {
        if (empty($this->_tasks)) {
            $this->_tasks = [];
            if (0 < $this->_task_id) {
                $where = ' `task_id` = :task_id ';
                $param = ['task_id' => $this->_task_id];
            } else {
                $where = ' `time_ignore` <= :time and `time_begin` <= :time and `time_end` >= :time  ';
                $param = ['i:time' => time()];
            }
            $rs = static::$_db_biz->table(static::$table_task)->field('*')->where($where, $param)->column_all();
            // static::$_db_task->stmt()->debugDumpParams();
            // print_r(['static::$_db_task->stmt()->queryString'=>static::$_db_task->stmt()->queryString,'$rs'=>$rs]);
            foreach ($rs as $v) {
                if ($v && $v['task_id'] && $v['task_class']) {
                    $cls = '\\' . $v['task_class'];
                    if (class_exists($cls)) {
                        $struct = new struct($v);
                        /** @var task_base $task */
                        $task = new $cls($struct);
                        if (is_subclass_of($task, "ounun\\cmd\\task\\task_base")) {
                            $this->_tasks[$v['task_id']] = $task;
                        } else {
                            console::echo("error --> class:{$cls} not subclass:task\\task_base", console::Color_Red);
                        }
                    } else {
                        console::echo("error --> class_exists:{$cls}", console::Color_Red);
                    }
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
        if (empty($this->_process)) {
            $this->_process = [];
            $rs = self::$_db_biz->table(static::$table_process)->field('*')->column_all();
            foreach ($rs as $v) {
                if ($v && $v['task_id']) {
                    $this->_process[$v['task_id']] = $v;
                }
            }
        }
        return $this->_process;
    }
}