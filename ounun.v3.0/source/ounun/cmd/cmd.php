<?php

namespace ounun\cmd;


use ounun\pdo;

abstract class cmd
{
    /** @var pdo */
    protected $_db;

    /** @var console 控制台 */
    public $console;

    /** @var string 命令的名字（"ounun" 后面的部分） */
    public $name;

    /** @var string 运行命令时使用 "--help" 选项时的完整命令描述 */
    public $help;

    /** @var string 运行 "php ./ounun list" 时的简短描述 */
    public $description;

    /**
     * cmd constructor.
     * @param console $console
     */
    public function __construct(console $console)
    {
        $this->console = $console;
        $this->configure();
    }

    /**
     * 是否有效
     * @return bool
     */
    public function is_enabled()
    {
        return true;
    }

    /**
     * @param array $argv
     */
    public function help(array $argv)
    {
        console::echo("命令:", console::Color_Purple, '',0,0,'');
        console::echo("({$this->description})");
        console::echo('./ounun ' . $this->name . ' [参数...]', console::Color_Blue);
        console::echo($this->help, console::Color_Purple);
    }

    /**
     * 配置指令
     */
    abstract public function configure();

    /**
     * 执行指令
     * @param array $input
     * @return null|int
     * @throws \LogicException
     */
    abstract public function execute(array $input);

}