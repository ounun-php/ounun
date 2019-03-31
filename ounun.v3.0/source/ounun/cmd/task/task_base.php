<?php
namespace ounun\cmd\task;

abstract class task_base
{

    /** @var struct 任务数据结构  */
    protected $_task_struct;

    /** @var int 模式  0:采集全部  1:检查 2:更新   见 \task\manage::mode_XXX */
    protected $_mode          =  manage::Mode_Check;
    /** @var string 分类 */
    protected $_tag           = '';
    /** @var string 子分类 */
    protected $_tag_sub       = '';

    /** @var bool 是否运行过 */
    protected $_is_run        = false;

    /**
     * task_base constructor.
     * @param struct $task_struct
     * @param string $tag
     * @param string $tag_sub
     */
    public function __construct(struct $task_struct,string $tag = '',string $tag_sub = '')
    {
        $this->_task_struct   = $task_struct;
        $this->tag_set($tag,$tag_sub);
    }

    /**
     * @param string $tag
     * @param string $tag_sub
     */
    public function tag_set(string $tag = '',string $tag_sub = '')
    {
        $this->_tag           = $tag;
        $this->_tag_sub       = $tag_sub;
    }

    /**
     * @return struct
     */
    public function struct()
    {
        return $this->_task_struct;
    }

    /**
     * 检查 执行
     * @param bool $is_pass_check
     * @return array|bool
     */
    public function check(bool $is_pass_check = false)
    {
        if($this->_task_struct && is_subclass_of($this->_task_struct, "ounun\\cmd\\task\\struct")  ){
            $time_curr = time();
            $rs = $this->_task_struct->check($time_curr,$is_pass_check);
            if(error_is($rs)){
                return $rs;
            }
            $this->_task_struct->update($time_curr);
            return true;
        }
        return error('task_struct数据有误。');
    }

    /**
     * 执行任务
     * @param array $input
     * @param int $mode
     * @param bool $is_pass_check
     */
    public function execute(array $input = [], int $mode = manage::Mode_Dateup,bool $is_pass_check = false)
    {
        if( !$this->check($is_pass_check) ) {
            return ;
        }

        $this->logs_init();
        sleep(rand(4,10));

        manage::$logs_state = manage::Logs_Fail;
        $this->logs_msg("Fail 没有任务",manage::Logs_Fail);
    }

    /**
     * 执行完成
     * @param float $run_time
     */
    public function done(float $run_time)
    {
        if( $this->_is_run )
        {
            $bind = [
                'task_id'     => $this->_task_struct->task_id,
                'time_ignore' => $this->_task_struct->time_ignore,
                'time_last'   => $this->_task_struct->time_last,
            ];
            $this->_is_run  = false;
            manage::logs()->extend_set(['count'=>$this->_task_struct->count]);
            manage::logs()->write(manage::$logs_state,$run_time,true);
            manage::db()->query( " UPDATE `".manage::$table_task."` SET `time_ignore` = :time_ignore ,`time_last` = :time_last ,`count` = `count` + 1 WHERE `task_id` = :task_id; ",$bind)->affected();
        }
    }

    /**
     * @param int $time
     */
    public function logs_init( int $time = 0)
    {
        manage::logs_init($this->_task_struct->task_id,$this->_tag,$this->_tag_sub,$time,manage::$table_logs,manage::db());
    }

    /**
     * @param string $msg
     * @param int $state
     * @param int $time
     */
    public function logs_msg(string $msg, int $state= manage::Logs_Normal,int $time = 0)
    {
        manage::logs_msg($state,$time,$msg);
    }
}
