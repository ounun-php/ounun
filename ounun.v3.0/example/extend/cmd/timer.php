<?php

namespace extend\cmd;

use ounun\cmd\console;

class timer extends \ounun\cmd\cmd
{
    public function configure()
    {
        // 命令的名字（"think" 后面的部分）
        $this->name = 'adm.timer';
        // 运行 "php think list" 时的简短描述
        $this->description = '定时器';
        // 运行命令时使用 "--help" 选项时的完整命令描述
        $this->help = "定时器列表\n" .
            "./ounun adm:tools oss [6mm,99mm] 文件移动\n";
    }

    public function execute(array $argc_input)
    {
        $method_default = 'timer_5m';
        $method = $argc_input[2] ? 'timer_' . $argc_input[2] : $method_default;
        if (method_exists($this, $method)) {
            $this->$method($argc_input);
        } else {
            console::echo("找不到\"{$method}\",执行默认:\"{$method_default}\"", console::Color_Red);
            $this->$method_default($argc_input);
        }
    }

    /**
     * 每五分钟调一次
     * **:*5 或 **:*0
     */
    public function timer_5m(array $input)
    {

        echo date("Y-m-d H:i:s ") . ' ' . __METHOD__ . ' ok' . "\n";
    }

    /**
     * 每一个小时调一次
     * **:59
     */
    public function timer_1h(array $input)
    {
        echo date("Y-m-d H:i:s ") . ' ' . __METHOD__ . ' ok' . "\n";
    }


    /**
     * 每四小时调一次
     * *4:58
     */
    public function timer_4h(array $input)
    {
        echo date("Y-m-d H:i:s ") . ' ' . __METHOD__ . ' ok' . "\n";
    }


    /**
     * 每12小时调一次
     * 03:57  15:57
     */
    public function timer_12h(array $input)
    {
        echo date("Y-m-d H:i:s ") . ' ' . __METHOD__ . ' ok' . "\n";
    }

    /**
     * 每天23：59：00点调一次
     */
    public function timer_1d(array $input)
    {
        echo date("Y-m-d H:i:s ") . ' ' . __METHOD__ . ' ok' . "\n";
    }
}
