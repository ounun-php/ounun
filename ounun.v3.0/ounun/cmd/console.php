<?php
namespace ounun\cmd;


class console
{
    /** @var string 命令名称 */
    protected $name;

    /** @var string 命令版本 */
    protected $version;

    /** @var cmd[] 命令 */
    private $commands = [];

    /** @var string 默认执行的命令 */
    protected $default_cmd;

    /** @var array  默认提供的命令 */
    protected static $default_cmds = [
        "\\ounun\\cmd\\def\\help",
        "\\ounun\\cmd\\def\\lists",
        "\\ounun\\cmd\\def\\test",
    ];


    public function __construct(array $cmds,string $name = 'Ounun CMD',string $version = '0.1')
    {
        echo "\\ounun\\cmd\\def\\help::class:".\ounun\cmd\def\help::class;
        $this->name    = $name;
        $this->version = $version;

        $cmds = self::$default_cmds + $cmds;
        if (is_array($cmds)) {
            foreach ($cmds as $cmd) {
                class_exists($cmd) &&
                is_subclass_of($cmd, "\\ounun\\cmd\\cmd") &&
                $this->add(new $cmd());  // 注册指令
            }
        }
    }

    /**
     * 添加一个指令
     * @param cmd $cmd 命令实例
     * @return bool|cmd
     */
    public function add(cmd $cmd)
    {
        if (!$cmd->is_enabled()) {
            $cmd->console = null;
            return false;
        }

        $cmd->console = $this;
        $this->commands[$cmd->name] = $cmd;
        return $cmd;
    }

    /**
     * 执行
     * @param array $argv
     * @return int
     */
    public function run(array $argv)
    {
        $statusCode = $this->execute($argv);
        return 0;
    }
}