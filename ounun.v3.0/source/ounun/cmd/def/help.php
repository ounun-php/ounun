<?php
namespace ounun\cmd\def;

use ounun\cmd\cmd;
use ounun\cmd\console;

class help extends cmd
{
    public function configure()
    {
        // 命令的名字（"think" 后面的部分）
        $this->name        = 'help';
        // 运行 "php think list" 时的简短描述
        $this->description = 'Display this help message';
        // 运行命令时使用 "--help" 选项时的完整命令描述
        $this->help        = "Displays help for a command";
    }

    /**
     * @param array $input
     * @return int|null|void
     */
    public function execute(array $input)
    {
        /** @var cmd $c */
        console::echo("可执行命令:",console::Color_Yellow);
        foreach ($this->console->commands as $c){
            console::echo($c->name,console::Color_Light_BBlue," \t");
            console::echo($c->description,console::Color_Dark_Gray);
        }
        console::echo('帮助',console::Color_Yellow, ' ');
        console::echo('./ounun <命令> --help',console::Color_Black,'  ');
        console::echo('显示对应"命令"提示',console::Color_Yellow);
    }
}