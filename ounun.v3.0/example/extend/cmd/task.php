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
     * @param array $input
     */
    public function execute(array $input)
    {
        //设置运存
        ini_set('memory_limit', -1);

        // 设定参数
        $input_len = 0;
        if ($input && is_array($input)) {
            $input_len = count($input);
        }
        if ($input_len >= 2) {
            array_shift($input);
            array_shift($input);
        }
        $mode = ($input_len >= 3) ? ((int)array_shift($input)) : manage::Mode_Dateup;
        $task_id = ($input_len >= 4) ? ((int)array_shift($input)) : 0;
        $time_sleep = ($input_len >= 5) ? ((int)array_shift($input)) : 0;
        $time_live = ($input_len >= 6) ? ((int)array_shift($input)) : 0;

        // instance
        $db_biz = pdo::instance(config::database_default_get() );
        $manage = manage::instance($db_biz);
        // 设定表名
        manage::table_set();
        // status
        $status = $manage->status();
        console::print_r(succeed_data($status));
        // execute
        $manage->execute($task_id, $mode, $time_sleep, $time_live, $input);
        // ok
        console::print_r("---> " . date("Y-m-d H:i:s ") . ' ' . __CLASS__ . ' execute ok');
    }
}
