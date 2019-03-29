<?php
/**
 * Created by PhpStorm.
 * User: dreamxyp
 * Date: 2019/1/14
 * Time: 01:03
 */
namespace extend\cmd;

class coll extends \ounun\cmd\cmd
{
    public function configure()
    {
        // 命令的名字（"think" 后面的部分）
        $this->name        = 'adm.coll';
        // 运行 "php think list" 时的简短描述
        $this->description = '采集任务进程任务';
        // 运行命令时使用 "--help" 选项时的完整命令描述
        $this->help        = "采集列表\n".
            "./ounun adm.coll [更新,检查,全部] [任务ID] \t采集任务\n";
    }

    public function execute(array $input)
    {
        // 打包下载
        echo "\n ---> ".date("Y-m-d H:i:s ").' '.__CLASS__.' ok'."\n";
    }
}