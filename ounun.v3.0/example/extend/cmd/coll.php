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
        $this->name        = 'coll';
        // 运行 "php think list" 时的简短描述
        $this->description = 'Display this help message';
        // 运行命令时使用 "--help" 选项时的完整命令描述
        $this->help        = "Displays help for a command";
    }

    public function execute(array $input)
    {
        // 打包下载
        echo "\n ---> ".date("Y-m-d H:i:s ").' '.__CLASS__.' ok'."\n";
    }
}