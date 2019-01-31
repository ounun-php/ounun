<?php
namespace ounun\cmd\def;

class lists extends \ounun\cmd\base
{
    protected function configure()
    {
        // 命令的名字（"think" 后面的部分）
        $this->name        = 'list';
        // 运行 "php think list" 时的简短描述
        $this->description = 'Lists commands';
        // 运行命令时使用 "--help" 选项时的完整命令描述
        $this->help        = "Lists commands";
    }

    protected function execute(array $input)
    {
        // 打包下载
        echo "\n ---> ".date("Y-m-d H:i:s ").' '.__CLASS__.' ok'."\n";
    }

}