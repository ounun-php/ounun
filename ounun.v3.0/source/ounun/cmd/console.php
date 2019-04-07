<?php

namespace ounun\cmd;


class console
{
    /** @var string 默认执行的命令 */
    const Default_Cmd = 'help';

    const Color_None = "\033[0m";
    const Color_Black = "\033[0;30m";
    const Color_Dark_Gray = "\033[1;30m";
    const Color_Blue = "\033[0;34m";
    const Color_Light_BBlue = "\033[1;34m";
    const Color_Green = "\033[0;32m";
    const Color_Light_Green = "\033[1;32m";
    const Color_Cyan = "\033[0;36m";
    const Color_Light_Cyan = "\033[1;36m";
    const Color_Red = "\033[0;31m";
    const Color_Light_Red = "\033[1;31m";
    const Color_Purple = "\033[0;35m";
    const Color_Light_Purple = "\033[1;35m";
    const Color_Brown = "\033[0;33m";
    const Color_Yellow = "\033[1;33m";
    const Color_Light_Gray = "\033[0;37m";
    const Color_White = "\033[1;37m";

    /** @var array 不同深度的颜色 */
    const Depth_Colors = [
        self::Color_Blue,
        self::Color_Black,
        self::Color_Green,
        self::Color_Cyan,
        self::Color_Red,
        self::Color_Purple,
        self::Color_Brown,
    ];
    /** @var int 深度的颜色数量 */
    const Depth_Colors_Count = 7;

    /** @var string 命令名称 */
    public $name;
    /** @var string 命令版本 */
    public $version;
    /** @var cmd[] 命令 */
    public $commands = [];

    /** @var array  默认提供的命令 */
    protected static $default_cmds = [
        "ounun\\cmd\\def\\help",
        "ounun\\cmd\\def\\test",
    ];

    /**
     * console constructor.
     * @param array $cmds
     * @param string $name
     * @param string $version
     */
    public function __construct(array $cmds, string $name = 'Ounun CMD', string $version = '0.1')
    {
        // echo "\\ounun\\cmd\\def\\help::class:".\ounun\cmd\def\help::class;
        $this->name = $name;
        $this->version = $version;

        $cmds = array_merge(self::$default_cmds, $cmds);
        if (is_array($cmds)) {
            foreach ($cmds as $cmd) {
                if (class_exists($cmd)) {
                    if (is_subclass_of($cmd, "ounun\\cmd\\cmd")) {
                        $this->add(new $cmd($this));  // 注册指令
                    }
                }
            }
        }
        // print_r(['$commands'=>$this->commands]);
    }

    /**
     * 添加一个指令
     * @param cmd $cmd 命令实例
     * @return bool|cmd
     */
    public function add(cmd $cmd)
    {
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
        // print_r(['$argv'=>$argv]);
        if (empty($argv) || empty($argv[1]) || '--help' == $argv[1] || '--list' == $argv[1]) {
            $command = $this->commands[self::Default_Cmd];
            $command->execute($argv);
        } else {
            $command = $this->commands[$argv[1]];
            if ($command) {
                if ('--help' == $argv[2]) {
                    $command->help($argv);
                } else {
                    $run_time = 0 - microtime(true);
                    $run_cmd = str_pad($argv[1], 16);
                    static::echo("-- runing... {$run_cmd} " . date("Y-m-d H:i:s") . "             --------------------", self::Color_Cyan);
                    $command->execute($argv);
                    $run_time += microtime(true);
                    static::echo("-- done      {$run_cmd} " . date("Y-m-d H:i:s") . " run:" . str_pad(round($run_time, 4) . 's', 8) . "--------------------", self::Color_Cyan);
                }
            } else {
                static::echo("命令:{$argv[1]} 不存在!", self::Color_Light_Red);
                static::echo("你可以尝试下面", self::Color_Green);
                $command = $this->commands[self::Default_Cmd];
                $command->execute($argv);
            }
        }
        return 0;
    }

    /**
     * none             = "\033[0m"  
     * black            = "\033[0;30m"  
     * dark_gray        = "\033[1;30m"  
     * blue             = "\033[0;34m"  
     * light_blue       = "\033[1;34m"  
     * green            = "\033[0;32m"  
     * light_green      = "\033[1;32m"  
     * cyan             = "\033[0;36m"  
     * light_cyan       = "\033[1;36m"  
     * red              = "\033[0;31m"  
     * light_red        = "\033[1;31m"  
     * purple           = "\033[0;35m"  
     * light_purple     = "\033[1;35m"  
     * brown            = "\033[0;33m"  
     * yellow           = "\033[1;33m"  
     * light_gray       = "\033[0;37m"  
     * white            = "\033[1;37m"  
     * 输出特效格式控制：  
     * \033[0m           关闭所有属性    
     * \033[1m           设置高亮度    
     * \033[4m           下划线    
     * \033[5m           闪烁    
     * \033[7m           反显    
     * \033[8m           消隐    
     * \033[30m   --   \033[37m   设置前景色    
     * \033[40m   --   \033[47m   设置背景色  
     * 字背景颜色范围: 40--49     字颜色: 30--39  
     * 40: 黑            30: 黑  
     * 41:红             31: 红  
     * 42:绿             32: 绿  
     * 43:黄             33: 黄  
     * 44:蓝             34: 蓝  
     * 45:紫             35: 紫  
     * 46:深绿           36: 深绿  
     * 47:白色           37: 白色  
     * 光标位置等的格式控制：  
     * \033[nA             光标上移n行    
     * \03[nB              光标下移n行    
     * \033[nC             光标右移n行    
     * \033[nD             光标左移n行    
     * \033[y;xH           设置光标位置    
     * \033[2J             清屏   
     * \033[K              清除从光标到行尾的内容    
     * \033[s              保存光标位置    
     * \033[u              恢复光标位置    
     * \033[?25l           隐藏光标    
     * \33[?25h            显示光标  
     */
    /**
     * @param string $msg
     * @param string $color
     * @param string $end
     */
    static public function echo(string $msg, string $color = '', string $end = "\n")
    {
        if (empty($color)) {
            echo $msg . $end;
        } else {
            echo $color . $msg . self::Color_None . $end;
        }
    }

    /**
     * @param $array
     * @param string $tab
     * @param int $depth0
     */
    static public function print_r($array, $tab = '', int $depth0 = 0)
    {

        $depth = $depth0 % static::Depth_Colors_Count;
        $color = static::Depth_Colors[$depth];
        // echo "\$depth:{$depth} - ";
        if (is_array($array)) {
            if (empty($array)) {
                static::echo("[]", $color);
            } else {
                static::echo("[", $color);
                foreach ($array as $k => $v) {
                    static::echo("\t" . $tab . (is_numeric($k) ? $k : '"' . $k . '"'), $color, '');
                    static::echo(' => ', static::Color_Light_Gray, '');
                    static::print_r($v, "\t" . $tab, $depth + 1);
                }
                static::echo($tab . "]", $color);
            }
        } else {
            static::echo(is_numeric($array) ? $array : '"' . $array . '"', $color);
        }
    }
}
