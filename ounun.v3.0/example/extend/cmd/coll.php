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
use ounun\pdo;

class coll extends \ounun\cmd\cmd
{
    public function configure()
    {
        $h = [];
        foreach (manage::Mode as $k => $v){
            $h[] = "{$k}:{$v}";
        }
        // 命令的名字（"think" 后面的部分）
        $this->name        = 'adm.coll';
        // 运行 "php think list" 时的简短描述
        $this->description = '采集任务进程任务';
        // 运行命令时使用 "--help" 选项时的完整命令描述
        $this->help        = "采集任务\n".
            "./ounun adm.coll [".implode(',',$h)."] [任务ID]\n";
    }

    public function execute(array $input)
    {
        //设置运存
        ini_set('memory_limit', -1);

        // 设定参数
        $mode     = isset($input[2])?(int)$input[2]:manage::Mode_Dateup;
        $task_id  = isset($input[3])?(int)$input[3]:0;

        // instance
        $db_biz   = pdo::instance('biz');
        $manage   = manage::instance($db_biz);
        // status
        $status   = $manage->status();
        console::print_r($status);
        // execute
        $manage->execute($task_id,$mode,  $input);
        // ok
        console::print_r("---> ".date("Y-m-d H:i:s ").' '.__CLASS__.' execute ok');
    }

    public function f()
    {
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
