<?php
/**
 * Created by PhpStorm.
 * User: dreamxyp
 * Date: 2019/1/14
 * Time: 01:03
 */

namespace extend\cmd;

use ounun\cmd\console;
use ounun\cmd\task\manage;
use ounun\config;
use ounun\pdo;

class task extends \ounun\cmd\cmd
{
    public function configure()
    {
        $h = [];
        foreach (manage::Mode as $k => $v) {
            $h[] = "{$k}:{$v}";
        }
        // 命令的名字（"think" 后面的部分）
        $this->name = 'adm.task';
        // 运行 "php think list" 时的简短描述
        $this->description = '任务进程任务';
        // 运行命令时使用 "--help" 选项时的完整命令描述
        $this->help = "任务\n" .
            "./ounun {$this->name} [" . implode(',', $h) . "] [任务ID] [间隔(秒,默认5秒)] [寿命(秒,默认300秒)] [网站tag]\n";
    }

    /**
     * @param array $argc_input
     */
    public function execute(array $argc_input)
    {
        //设置运存
        ini_set('memory_limit', -1);

        // 设定参数
        $input_len = 0;
        if ($argc_input && is_array($argc_input)) {
            $input_len = count($argc_input);
        }
        if ($input_len >= 2) {
            array_shift($argc_input);
            array_shift($argc_input);
        }
        $argc_mode = ($input_len >= 3) ? ((int)array_shift($argc_input)) : manage::Mode_Dateup;
        $argc_task_id = ($input_len >= 4) ? ((int)array_shift($argc_input)) : 0;
        $argc_time_sleep = ($input_len >= 5) ? ((int)array_shift($argc_input)) : 0;
        $argc_time_live = ($input_len >= 6) ? ((int)array_shift($argc_input)) : 0;

        // instance
        $db_biz = pdo::instance(config::database_default_get() );
        $manage = manage::instance($db_biz);
        // 设定表名
        manage::table_set();
        // status
        $manage->init();
        $status = $manage->status();
        console::print_r($status);
        // execute
        $manage->execute($argc_task_id, $argc_mode, $argc_time_sleep, $argc_time_live, $argc_input);
        // ok
        console::echo("-- " . date("Y-m-d H:i:s ") . ' ' . __CLASS__ . ' execute ok',console::Color_Blue);
    }
}
