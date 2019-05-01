<?php

namespace ounun\cmd\def;

use ounun\cmd\cmd;
use ounun\cmd\console;

class help extends cmd
{
    public function configure()
    {
        // 命令的名字（"think" 后面的部分）
        $this->name = 'help';
        // 运行 "php think list" 时的简短描述
        $this->description = 'Display this help message';
        // 运行命令时使用 "--help" 选项时的完整命令描述
        $this->help = "Displays help for a command";
    }

    /**
     * @param array $input
     * @return int|null|void
     */
    public function execute(array $argc_input)
    {
        $cs = [];
        foreach ($this->console->commands as $c) {
            $n = explode('.', $c->name, 2);
            if (2 == count($n)) {
                $cs[$n[0]][] = $c;
            } else {
                $cs['0'][] = $c;
            }
        }
        $cc = count($cs);
        $i = 0;
        /** @var cmd $c */
        console::echo("\n可执行命令:", console::Color_Purple);
        foreach ($cs as $v) {
            foreach ($v as $c) {
                console::echo($c->name, console::Color_Blue, '',0,0," \t");
                console::echo($c->description, console::Color_Black);
            }
            $i++;
            if ($i < $cc) {
                echo "\n";
            }
        }
        console::echo('帮助', console::Color_Purple, '',0,0,' ');
        console::echo('./ounun <命令> --help', console::Color_Black,'',0,0, '  ');
        console::echo('显示对应"命令"提示', console::Color_Purple, '',0,0,"\n\n");
    }
}
