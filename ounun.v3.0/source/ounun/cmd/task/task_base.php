<?php

namespace ounun\cmd\task;


use extend\config_cache;
use ounun\cmd\console;
use ounun\config;
use ounun\mvc\model\admin\purview;
use ounun\pdo;

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
    protected $_argc_mode = manage::Mode_Check;


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
     * @param array $argc_input
     * @param int $argc_mode
     * @param bool $is_pass_check
     * @return int
     */
    public function execute_do(array $argc_input = [], int $argc_mode = manage::Mode_Dateup, bool $is_pass_check = false)
    {
        $rs = $this->check($is_pass_check);
        if (error_is($rs)) {
            console::echo(error_message($rs), console::Color_Red, __FILE__, __LINE__, time(), ' ');
            console::echo(" task_id:{$this->struct_get()->task_id} name:{$this->tag_get()}/{$this->struct_get()->task_name}", console::Color_Brown, '', 0, 0, ' ');
            console::echo(get_class($this), console::Color_Blue);
            return 0;
        }
        // start
        $this->_run_time = 0 - microtime(true);
        $this->_argc_mode = $argc_mode;
        $this->_run_is = true;
        // init
        $this->_db_init();
        manage::logs_init();
        $this->_db_update(manage::Status_Runing);
        // logs
        console::echo("↓↓↓ 任务开始 ↓↓↓ --> task_id:{$this->struct_get()->task_id} name:{$this->tag_get()}/{$this->struct_get()->task_name}", console::Color_Green, __FILE__, __LINE__, time(), ' ');
        console::echo(get_class($this), console::Color_Blue);
        // execute
        $this->execute($argc_input, $argc_mode, $is_pass_check);
        // _run_time
        $this->_run_time += microtime(true);
        $this->_db_done();
        console::echo("↑↑↑ 任务结束 ↑↑↑ <-- task_id:{$this->struct_get()->task_id} logs_id:".manage::logs_id_get()." ", console::Color_Green, __FILE__, __LINE__, time(), '');
        console::echo('运行时间:' . str_pad(round($this->run_time_get(), 4) . 's', 8), console::Color_Light_Purple,'',0,0,"\n\n");
        return $this->struct_get()->task_id;
    }

    /**
     * 执行任务
     * @param array $argc_input
     * @param int $argc_mode
     * @param bool $is_pass_check
     */
    abstract public function execute(array $argc_input = [], int $argc_mode = manage::Mode_Dateup, bool $is_pass_check = false);

    /**
     * 返回运行状态
     * @return array
     */
    public function status()
    {
        $this->_logs_status = manage::Logs_Fail;
        manage::logs_msg("class:" . get_class($this), $this->_logs_status,__FILE__,__LINE__,time());
        return [];
    }

    /**
     * 检查 执行
     * @param bool $is_pass_check
     * @return array|bool
     */
    public function check(bool $is_pass_check = false)
    {
        if ($this->_task_struct && is_a($this->_task_struct, "ounun\\cmd\\task\\struct")) {
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
     * _db_init
     */
    public function _db_init()
    {
        if ($this->_run_is && $this->_task_struct) {
            $is_db_caiji = false;
            $is_db_site  = false;
            if($this->_task_struct->run_type == manage::Run_Type_Post){
                $is_db_caiji = true;
                $is_db_site  = true;
            }elseif($this->_task_struct->run_type == manage::Run_Type_System){
                $is_db_caiji = false;
                $is_db_site  = true;
            }else{ // manage::Run_Type_Caiji
                $is_db_caiji = true;
                $is_db_site  = false;
            }
            // caiji
            if($is_db_caiji && $this->_task_struct->caiji_tag){
                $caiji_tag = $this->_task_struct->caiji_tag;
                $libs = config::$global['caiji'][$caiji_tag];
                if ($libs && $libs['db'] && config::$database[$libs['db']]) {
                    manage::db_caiji(config::$database[$libs['db']]);
                }
            }
            // site
            if($is_db_site && $this->_task_struct->site_tag){
                $site_info = config_cache::instance(\c::Cache_Tag_Site, manage::db_biz())->site_info($this->_task_struct->site_tag);
                if ($site_info['config_db']) {
                    $db_cfg = json_decode($site_info['config_db'], true);
                    if ($db_cfg && $db_cfg['host']) {
                        manage::db_site($db_cfg); 
                    }
                }
            }
        }
    }

    /**
     * 执行完成
     */
    protected function _db_done()
    {
        if ($this->_run_is) {
            $this->_task_struct->update(time());
            $bind = [
                'task_id' => $this->_task_struct->task_id,
                'time_ignore' => $this->_task_struct->time_ignore,
                'time_last' => $this->_task_struct->time_last,

                'run_hostname' => gethostname(),
                'run_status'   => manage::Status_Await,
            ];
            $this->_run_is = false;
            manage::logs_extend_set(['count' => $this->_task_struct->count]);
            manage::db_biz()->query("UPDATE ".manage::$table_task." SET `run_hostname` = :run_hostname ,`run_status` = :run_status ,`time_ignore` = :time_ignore ,`time_last` = :time_last ,`count` = `count` + 1 WHERE `task_id` = :task_id; ", $bind)->affected();
        }
        manage::logs_write($this->_logs_status, $this->run_time_get(),true);
    }

    /**
     * @param int $run_status
     */
    protected function _db_update(int $run_status = manage::Status_Runing)
    {
        if ($this->_run_is) {
            $this->_task_struct->update(time());
            $bind = [
                'task_id' => $this->_task_struct->task_id,
                'time_ignore' => $this->_task_struct->time_ignore,
                'time_last' => $this->_task_struct->time_last,

                'run_hostname' => gethostname(),
                'run_status'   => $run_status,
            ];
            manage::db_biz()->query("UPDATE ".manage::$table_task." SET `run_hostname` = :run_hostname ,`run_status` = :run_status ,`time_ignore` = :time_ignore ,`time_last` = :time_last  WHERE `task_id` = :task_id; ", $bind)->affected();
        }
    }
}
