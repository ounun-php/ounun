<?php


namespace ounun\cmd;


use ounun\config;

abstract class cmd_multiple extends cmd
{
    /** @var int 间隔(秒,默认5秒) */
    protected $_time_argc_sleep = 5;
    /** @var int 寿命(秒,默认300秒) */
    protected $_time_argc_live = 59;
    /** @var int 当前时间 */
    protected $_time_curr = 0;
    /** @var int 过去的时间 */
    protected $_time_past = 0;
    /** @var int 执行次数 */
    protected $_time_run_count = 0;


    /**
     * @param array $argc_input
     * @return int
     */
    public function execute(array $argc_input)
    {
        // 设置运存
        ini_set('memory_limit', -1);

        // 设定参数
        $input_new = [];
        $input_len = 0;
        if ($argc_input && is_array($argc_input)) {
            $input_len = count($argc_input);
        }
        if ($input_len >= 1) {
            $input_new[] = array_shift($argc_input);
        }
        if ($input_len >= 2) {
            $input_new[] =  array_shift($argc_input);
        }
        $this->_time_argc_sleep = ($input_len >= 3) ? ((int)array_shift($argc_input)) : 5;
        $this->_time_argc_live  = ($input_len >= 4) ? ((int)array_shift($argc_input)) : 59;
        $this->_time_curr = time();
        $this->_time_past = 0;
        $this->_time_run_count = 0;
        if($input_len >= 5){
            while ($argc_input){
                $input_new[] =  array_shift($argc_input);
            }
        }

        // 每次只执行一次任务
        $do = 0;
        do {
            console::echo("Execute multiple  \$sleep:" . str_pad($this->_time_argc_sleep, 5) .
                                                 " \$count:" . str_pad($this->_time_run_count, 5) .
                                                 " \$past:" . str_pad($this->_time_past, 5) .
                                                 " \$live:" . str_pad($this->_time_argc_live, 5) .' ---------- ', console::Color_Light_Red, __FILE__, __LINE__);
            $do = $this->execute_do($input_new);
            if($do){
               return $do;
            }
            $this->_time_run_count++;
            if (0 == $do) {
                sleep($this->_time_argc_sleep);
            }
            $this->_time_past = time() - $this->_time_curr;
        }while ($this->_time_past < $this->_time_argc_live && 0 == $do);
        return 0;
    }

    /**
     * @param array $argc_input
     * @return int
     */
    public function execute_do(array $argc_input)
    {
        echo __METHOD__ . " \$input:".json_encode_unescaped($argc_input)."\n";
        return 0;
    }
}
