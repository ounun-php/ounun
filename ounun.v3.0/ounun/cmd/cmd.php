<?php
namespace ounun\cmd;


class cmd extends \ounun\base
{
    /** @var console 控制台 */
    public $console;

    /** @var string 命令的名字（"ounun" 后面的部分） */
    public $name;

    /** @var string 运行命令时使用 "--help" 选项时的完整命令描述 */
    public $help;

    /** @var string 运行 "php ./ounun list" 时的简短描述 */
    public $description;
    /**
     * 是否有效
     * @return bool
     */
    public function is_enabled()
    {
        return true;
    }

    /**
     * 配置指令
     */
    protected function configure()
    {

    }

    /**
     * 执行指令
     * @param array  $input
     * @return null|int
     * @throws \LogicException
     */
    protected function execute(array $input)
    {
        throw new \LogicException('You must override the execute() method in the concrete command class.');
    }
}