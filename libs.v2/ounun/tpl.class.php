<?php 
/** 命名空间 */
namespace ounun;

/**
 * 强制要求子类定义这些方法
 * Class _tpl
 * @package ounun
 */
abstract class _tpl
{

    /**
     * 模板根目录
     * @var string
     */
    protected $_dir_root;

    /**
     * 相对模板目录(当前目录)
     * @var string
     */
    protected $_dir_current;

    /**
     * 模板样式目录
     * @var string
     */
    protected $_style_name;

    /**
     * 模板文件所以目录(默认)
     * @var string
     */
    protected $_style_name_default;

    /**
     * 数据缓存
     * @var array
     */
    protected $_data;

    /**
     * 创建对像
     * @param string $dir_root
     */
    abstract public function __construct($dir_root = '', $style_name = '', $style_name_default = '');

    /**
     * 设定一个值
     * @param $name
     * @param null $value
     */
    abstract public function assign($name, $value=null);

    /**
     * 追加一个 值
     * @param $name
     * @param $value
     */
    abstract public function append($name, $value=null);

    /**
     * 返回一个 模板文件地址(相对目录)
     * @param $tpl_name
     */
    abstract public function file_cur($filename);

    /**
     * 返回一个 模板文件地址(绝对目录,相对root)
     * @param $tpl_name
     */
    abstract public function file($filename);

    /**
     * 返回一个 模板文件地址(兼容)
     * @param $tpl_name
     */
    abstract public function file_comp($filename);

    /**
     * 导入一个模板文件
     * @param $tpl_name
     * @param array $vars
     */
    abstract public function import($filename, $vars = []);

    /**
     * 最后输出
     * @param $tpl_name
     * @param array $vars
     */
    abstract public function output($filename, $vars = []);
}

/**
 * 模板类
 * @package core
 */
class tpl
{
    /**
     * 模板驱动名(默认)
     * @var string
     */
    const drive_name_default    = 'php';

    /**
     * 驱动缓存
     * @var _tpl
     */
    private static $_drive      = null;

    /**
     * 创建一个模板对像
     * Tpl constructor.
     * @param string $dir_root
     * @param string $style_name
     * @param string $style_name_default
     * @param string $drive_name
     * @param string $cache_lifetime
     */
    public function __construct($dir_root='', $style_name = '',$style_name_default='', $drive_name = '', $cache_lifetime = 0)
    {
        if('' == $drive_name)
        {
            $drive_name = self::drive_name_default;
        }
        $filename  = __DIR__. "/tpl_drive/{$drive_name}.class.php";

        if(file_exists($filename))
        {
            $this->_drive_name = $drive_name;

            require $filename;
            if('php' == $drive_name)
            {
                self::$_drive = new \ounun\tpl_drive\php($dir_root,$style_name,$style_name_default);
            }else
            {
                self::$_drive = new $drive_name($dir_root,$style_name,$style_name_default,$cache_lifetime);
            }
        }
        else
        {
            trigger_error("Error:Not found \"{$drive_name}\" template drive", E_USER_ERROR);
            exit();
        }
    }

    /**
     * Assign Template Content
     *
     * Usage Example:
     * $page->assign( 'TITLE',     'My Document Title' );
     * $page->assign( 'userlist',  array(
     *                                 array( 'ID' => 123,  'NAME' => 'John Doe' ),
     *                                 array( 'ID' => 124,  'NAME' => 'Jack Doe' ),
     *                             );
     *
     * @access public
     * @param string $name Parameter Name
     * @param mixed $value Parameter Value
     * @desc Assign Template Content
     *
     * 设定一个值 赋值
     * @param string|array $name
     * @param mix $value
     */
    public function assign($name, $value = '')
    {
        self::$_drive->assign($name, $value);
    }

    /**
     * Usage Example:
     * $page->append( 'userlist',  array( 'ID' => 123,  'NAME' => 'John Doe' ) );
     * $page->append( 'userlist',  array( 'ID' => 124,  'NAME' => 'Jack Doe' ) );
     *
     * 追加一个 值
     * @param $name
     * @param $value
     */
    public function append($name, $value)
    {
        self::$_drive->append($name, $value);
    }

    /**
     * 返回一个 模板文件地址(绝对目录,相对root)
     * @param $tpl_name
     */
    public static function file($filename)
    {
        return self::$_drive->file($filename);
    }

    /**
     * 返回一个 模板文件地址(相对目录)
     * @param $tpl_name
     */
    public static function file_cur($filename)
    {
        return self::$_drive->file_cur($filename);
    }

    /**
     * 返回一个 返回一个 模板文件地址(兼容)
     * @param $tpl_name
     */
    public static function file_comp($filename)
    {
        return self::$_drive->file_comp($filename);
    }

    /**
     * 导入一个模板文件
     * @param $tpl_name
     * @param array $vars
     */
    public static function import($filename,$args=[])
    {
        self::$_drive->import($filename,$args);
    }


    /**
     * 返回一个 返回一个 模板文件地址(兼容)
     * @param $tpl_name
     */
    public function require_file($filename)
    {
        return self::$_drive->file_comp($filename);
    }
    /**
     * 最后输出
     * @param $tpl_name
     * @param array $vars
     */
    public function output($filename,array $args=[])
    {
        self::$_drive->output($filename,$args);
    }
}
