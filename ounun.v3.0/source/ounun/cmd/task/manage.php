<?php

namespace ounun\cmd\task;

use ounun\cmd\console;
use ounun\config;
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

    /** @var string 采集 - 运行类型 */
    const Run_Type_Caiji = 'caiji';
    /** @var string 发布 - 运行类型 */
    const Run_Type_Post = 'post';
    /** @var string 系统 - 运行类型 */
    const Run_Type_System = 'system';
    /** @var array 运行类型(采集/发布/系统) */
    const Run_Type = [
        self::Run_Type_Caiji => '采集',
        self::Run_Type_Post => '发布',
        self::Run_Type_System => '系统',
    ];

    /** @var int 0:采集全部 */
    const Mode_All = 0; // 0:采集全部   1:检查   2:更新
    /** @var int 2:更新 */
    const Mode_Dateup = 1; // 2:更新
    /** @var int 1:检查 */
    const Mode_Check = 99; // 1:检查
    /** @var array 模式 0:采集全部   1:检查   2:更新 */
    const Mode = [
        self::Mode_All => '全部(默认,带任务ID非强制)',
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

    /** @var self 单例 */
    protected static $_instance_manage;
    /** @var \ounun\pdo */
    protected static $_db_biz;
    /** @var \ounun\pdo */
    protected static $_db_caiji;
    /** @var \ounun\pdo */
    protected static $_db_site;

    /** @var string 记日志DB */
    protected static $_tag_db_logs;
    /** @var string 记任务DB */
    protected static $_tag_db_task;
    /** @var string 采集Tag */
    protected static $_tag;

    /**
     * 返回数据库连接对像
     * @param pdo|null $db_biz
     * @param pdo|null $db_caiji
     * @param pdo|null $db_site
     * @param string $tag_db_logs
     * @param string $tag_db_task
     * @return manage
     */
    public static function instance(string $tag ,pdo $db_biz = null, pdo $db_caiji = null, pdo $db_site = null, string $tag_db_logs = 'caiji', string $tag_db_task = 'caiji'): self
    {
        if (empty(static::$_instance_manage)) {
            static::$_instance_manage = new static();
        }
        if ($db_biz) {
            static::$_db_biz = $db_biz;
        }
        if ($db_caiji) {
            static::$_db_caiji = $db_caiji;
        }
        if ($db_site) {
            static::$_db_site = $db_site;
        }
        static::$_tag         = $tag;
        static::$_tag_db_logs = $tag_db_logs;
        static::$_tag_db_task = $tag_db_task;
        return static::$_instance_manage;
    }

    /**
     * @param array $db_config
     * @return pdo 任务 数据库
     */
    public static function db_biz(array $db_config = [])
    {
        if (empty(static::$_db_biz)) {
            static::$_db_biz = pdo::instance('biz', $db_config);
        }
        return static::$_db_biz;
    }

    /**
     * @param array $db_config
     * @return pdo 采集 数据库
     */
    public static function db_caiji(array $db_config = [])
    {
        if (empty(static::$_db_caiji)) {
            static::$_db_caiji = pdo::instance('caiji', $db_config);
        }
        return static::$_db_caiji;
    }

    /**
     * @param array $db_config
     * @return pdo 站点 数据库
     */
    public static function db_site(array $db_config = [])
    {
        if (empty(static::$_db_site)) {
            static::$_db_site = pdo::instance('site', $db_config);
        }
        return static::$_db_site;
    }

    /**
     * @return pdo
     */
    public static function db_logs()
    {
        if('caiji' == static::$_tag_db_logs){
            return static::db_caiji();
        }elseif('biz' == static::$_tag_db_logs){
            return static::db_biz();
        }elseif('site' == static::$_tag_db_logs){
            return static::db_site();
        }
        return static::db_caiji();
    }

    /**
     * @return pdo
     */
    public static function db_task()
    {
        if('caiji' == static::$_tag_db_task){
            return static::db_caiji();
        }elseif('biz' == static::$_tag_db_task){
            return static::db_biz();
        }elseif('site' == static::$_tag_db_task){
            return static::db_site();
        }
        return static::db_caiji();
    }

    /**
     * 设定任务表与运行中的任务表
     * @param string $table_task               任务表
     * @param string $table_task_logs          日志的任务表
     * @param string $table_task_logs_details  日志详情
     */
    public static function table_set(string $table_task = '`caiji_task`',
                                     string $table_task_logs = '`caiji_task_logs`',
                                     string $table_task_logs_details = '`yst_logs`')
    {
        if ($table_task) {
            self::$table_task = $table_task;
        }
        static::logs_table_set($table_task_logs, $table_task_logs_details);
    }

    /**
     * @return int 返回模式
     */
    public function mode_get()
    {
        return $this->_argc_mode;
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
    static protected $_table_task_logs = '';
    /** @var string 表名 */
    static protected $_table_task_logs_details = '';

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
    /** @var int 日志id */
    static protected $_logs_id = 0;

    /**
     * @param int $time_add
     * @param string $table_task_logs
     * @param string $table_task_logs_details
     */
    static public function logs_init(int $time_add = 0,string $table_task_logs = '`caiji_task_logs`', string $table_task_logs_details = '`yst_logs`')
    {
        static::logs_table_set($table_task_logs, $table_task_logs_details);
        static::$_logs_data = [];
        static::$_logs_data_important = [];
        static::$_logs_time_add = $time_add == 0 ? time() : $time_add;
        static::$_logs_extend = [];
        static::$_logs_status = manage::Logs_Normal;
        static::$_logs_id = 0;
        static::logs_write_init();
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
     * @return int
     */
    static public function logs_id_get()
    {
        return static::$_logs_id;
    }

    /**
     * @param string $table_task_logs,
     * @param string $table_task_logs_details
     */
    static public function logs_table_set(string $table_task_logs = '', string $table_task_logs_details = '')
    {
        if ($table_task_logs) {
            static::$_table_task_logs = $table_task_logs;
        }
        if ($table_task_logs_details) {
            static::$_table_task_logs_details = $table_task_logs_details;
        }
    }

    /**
     * @param string $msg
     * @param string $file
     * @param int $line
     * @param int $time
     */
    static public function logs_msg_normal(string $msg, string $file = '', int $line = 0, int $time = 0)
    {
        static::logs_msg($msg, static::Logs_Normal, $file, $line, $time);
    }

    /**
     * @param string $msg
     * @param string $file
     * @param int $line
     * @param int $time
     */
    static public function logs_msg_fail(string $msg, string $file = '', int $line = 0, int $time = 0)
    {
        static::logs_msg($msg, static::Logs_Fail, $file, $line, $time);
    }

    /**
     * @param string $msg
     * @param string $file
     * @param int $line
     * @param int $time
     */
    static public function logs_msg_warning(string $msg, string $file = '', int $line = 0, int $time = 0)
    {
        static::logs_msg($msg, static::Logs_Warning, $file, $line, $time);
    }

    /**
     * @param string $msg
     * @param string $file
     * @param int $line
     * @param int $time
     */
    static public function logs_msg_succeed(string $msg, string $file = '', int $line = 0, int $time = 0)
    {
        static::logs_msg($msg, static::Logs_Succeed, $file, $line, $time);
    }

    /**
     * 日志数据logs_data
     * @param string $msg 内容
     * @param int $status 状态  0:正常(灰) 1:失败(红色) 6:突出(橙黄)  99:成功(绿色)
     * @param string $file
     * @param int $line
     * @param int $time 时间
     */
    static public function logs_msg(string $msg, int $status = self::Logs_Normal, string $file = '', int $line = 0, int $time = 0)
    {
        static::logs_write_init($status);

        $time = $time == 0 ? time() : $time;
        /**  状态  时间 内容  */
        $data = ['s' => $status, 't' => $time, 'l' => $msg];
        if (static::Logs_Fail == $status || static::Logs_Warning == $status || static::Logs_Succeed == $status) {
            static::$_logs_data_important[] = $data;
        }
        static::$_logs_data[] = $data;
        if (static::$_logs_id) {
            $db = static::db_logs();
            if ($db) {
                $db->table(static::$_table_task_logs_details)->insert(array_merge(['logs_id' => static::$_logs_id], $data));
            }
        }
        if (static::Logs_Fail == $status) {
            $color = console::Color_Red;
        } elseif (static::Logs_Warning == $status) {
            $color = console::Color_Brown;
        } elseif (static::Logs_Succeed == $status) {
            $color = console::Color_Green;
        } else {
            $color = console::Color_Yellow;
        }
        console::echo($msg, $color, $file, $line, $time, "\n");
    }

    /**
     * 写入日志
     * @param int $status
     * @param float $run_time
     * @param bool $over_clean 写完是否清理logs数据
     */
    static public function logs_write(int $status, float $run_time, bool $over_clean = true)
    {
        static::logs_write_init($status);

        if (static::$_logs_id) {
            $bind = [
                'status' => $status,
                'data' => json_encode(static::$_logs_data_important, JSON_UNESCAPED_UNICODE),
                'time_end' => time(),
                'time_run' => $run_time,
                'extend' => json_encode(static::$_logs_extend, JSON_UNESCAPED_UNICODE),
            ];
            $db = static::db_logs();
            if ($db) {
                $db->table(static::$_table_task_logs)->where(' `logs_id` = :logs_id ', ['logs_id' => static::$_logs_id])->update($bind);
            }
        }
    }

    /**
     * 写入日志 - 开始
     * @param int $status
     */
    static protected function logs_write_init(int $status = self::Logs_Normal)
    {
        if (static::$_logs_id) {
            return;
        }
        $manage = static::instance(static::$_tag);
        if ($manage && $manage->task_curr_get() && $manage->task_curr_get()->struct_get()) {
            $task_id = $manage->task_curr_get()->struct_get()->task_id;
            $task_curr = $manage->task_curr_get();
            if ($task_id) {
                $bind = [
                    'task_id' => $task_id,
                    'tag' => $task_curr->tag_get(),
                    'tag_sub' => $task_curr->tag_sub_get(),
                    'status' => $status,
                    'data' => json_encode(static::$_logs_data_important, JSON_UNESCAPED_UNICODE),
                    'time_add' => static::$_logs_time_add,
                    'time_end' => 0,
                    'time_run' => 0,
                    'extend' => json_encode(static::$_logs_extend, JSON_UNESCAPED_UNICODE),
                ];
                $db = static::db_logs();
                if ($db) {
                    static::$_logs_id = $db->table(static::$_table_task_logs)->insert($bind);
                }
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

    /** @var task_base 当前执行中的任务 */
    protected $_task_curr;

    /** @var array<task_id,task> 所有触发的任务Map */
    protected $_tasks = [];
    /** @var array 运行状态 */
    protected $_task_status = [
//        'task_id' => '',//
//        'status' => '',
//        'mode' => '',
//        'runing' => 0, // 运行中的任务
    ];

    /** @var int 任务ID */
    protected $_argc_task_id = 0;
    /** @var int 模式 */
    protected $_argc_mode = self::Mode_Dateup;
    /** @var int 间隔(秒,默认5秒) */
    protected $_argc_sleep_time = 5;

    /**  */
    public function init()
    {
        if (empty($this->_task_status) || empty($this->_tasks)) {
            $this->tasks();
        }
    }

    /**
     * 返回运行状态
     * @return array
     */
    public function status()
    {
        // 正在运行中的任务task_ids
        return $this->_task_status;
    }

    /**
     * 执行任务
     * @param int $argc_task_id     任务
     * @param int $argc_mode        运行模式
     * @param int $argc_sleep_time
     * @param array $argc_input
     * @return int
     */
    public function execute(int $argc_task_id, int $argc_mode,int $argc_sleep_time, array $argc_input = [])
    {
        $is_pass_check = ($argc_task_id && $argc_mode) ? true : false;

        $this->_argc_task_id = $argc_task_id;
        $this->_argc_mode = $argc_mode;
        $this->_argc_sleep_time = $argc_sleep_time;

        /**
         * 1. 同主机 最高任务并发为 config::$global['task_parallel_max']
         * 2. 同任务ID,同时只能单例运行
         * 3. 每次只执行一次任务
         */
        $this->init();
        $tasks = $this->tasks();
        console::echo("Start   host:" . gethostname() . "  task_parallel_max:" . config::$global['task_parallel_max'] . "    \$tasks_count:" . str_pad(count($tasks), 5) .
                           ' ----------------- ', console::Color_Purple, __FILE__, __LINE__);
        /** @var task_base $task */
        foreach ($tasks as $task) {
            // var_dump(['$task'=>$task]);
            if ($task && is_subclass_of($task, "ounun\\cmd\\task\\task_base")) {
                $this->_task_curr = $task;
                $do = $this->_task_curr->execute_do($argc_input, $this->_argc_mode, $is_pass_check);
                if($do){
                    return $do;
                }
            }
        }
        return 0;
    }

    /**
     * 返回符合条件的任务列表
     * @param bool $force_refresh
     * @return array
     */
    public function tasks(bool $force_refresh = false)
    {
        /**
         * 1.empty($this->_tasks)
         * 2.empty($this->_task_status)
         * 3.$force_refresh = true
         * 4.time() - $this->_task_status[time_create] > $this->_time_argc_sleep   &&  $force_refresh = false
         */
        if (empty($this->_tasks)
            || empty($this->_task_status)
            || $force_refresh
            || ($this->_task_status && (time() - $this->_task_status['time_create'] > $this->_argc_sleep_time))) {
            // --------------------------------------------------------------------
            $this->_tasks = [];
            if (0 < $this->_argc_task_id) {
                $where = ' `task_id` = :task_id ';
                $param = ['task_id' => $this->_argc_task_id];
            } else {
                $where = ' `time_ignore` <= :time and `time_begin` <= :time and `time_end` >= :time  ';
                $param = ['i:time' => time()];
            }
            $cc_bind = [
                's:run_hostname' => gethostname(),
                'i:run_status' => manage::Status_Runing,
                'i:time' => time()
            ];
            $db = static::db_task();
            $cc = $db->query('SELECT (SELECT count(`task_id`) FROM `caiji_task` WHERE `run_hostname` = :run_hostname and `run_status` = :run_status ) as `run_curr` ,  
                                                       (SELECT count(`task_id`) FROM `caiji_task` WHERE `run_status` = :run_status ) as `run_curr_all` , 
                                                       (SELECT count(`task_id`) FROM `caiji_task` WHERE `time_ignore` <= :time and `time_begin` <= :time and `time_end` >= :time ) as `task_count`;', $cc_bind)->column_one();
            $rs = $db->table(static::$table_task)->field('*')->where($where, $param)->column_all();
            // static::$_db_biz->stmt()->debugDumpParams();
            // print_r(['static::$_db_task->stmt()->queryString'=>static::$_db_biz->stmt()->queryString,'$rs'=>$rs]);
            foreach ($rs as $v) {
                if ($v && $v['task_id'] && $v['task_class']) {
                    $cls = '\\' . $v['task_class'];
                    if (class_exists($cls)) {
                        $struct = new struct($v);
                        /** @var task_base $task */
                        $task = new $cls($struct);
                        // console::echo($cls, console::Color_Blue, __FILE__, __LINE__, time());
                        if (is_subclass_of($task, "ounun\\cmd\\task\\task_base")) {
                            $this->_tasks[$v['task_id']] = $task;
                        } else {
                            console::echo("error --> class:{$cls} not subclass:task\\task_base", console::Color_Red, __FILE__, __LINE__, time());
                        }
                    } else {
                        console::echo("error --> class_exists:{$cls}", console::Color_Red, __FILE__, __LINE__, time());
                    }
                }
            }
            // --------------------------------------------------------------------
            $this->_task_status = [
                'tag' => static::$_tag,
                'argc_mode' => $this->_argc_mode,
                'argc_task_id' => $this->_argc_task_id,
                'argc_sleep_time' => $this->_argc_sleep_time,
                'time_create' => time(),
                'task_count' => $cc['task_count'],
                'run_curr' => $cc['run_curr'],
                'run_curr_all' => $cc['run_curr_all'],
            ];
        }
        return $this->_tasks;
    }
}