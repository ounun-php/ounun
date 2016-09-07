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
     * 模板文件所以目录
     * @var string
     */
    protected $_tpl_dir;
    
    /**
     * 模板文件所以目录(当前目录)
     * @var string
     */
    protected $_tpl_dir_cur;
    
    /**
     * 数据缓存
     * @var array
     */
    protected $_data;
    
    /**
     * 创建对像
     * @param string $template_dir
     */
    abstract public function __construct($template_dir = null);
    
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
    abstract public function append($name, $value);
    
    /**
     * 返回一个 模板文件地址(相对目录)
     * @param $tpl_name
     */
    abstract public function file_cur($tpl_name);
    
    /**
     * 返回一个 模板文件地址(绝对目录,相对root)
     * @param $tpl_name
     */
    abstract public function file($tpl_name);
    
    /**
     * 导入一个模板文件
     * @param $tpl_name
     * @param array $vars
     */
    abstract public function import($tpl_name, $vars = array());
    
    /**
     * 最后输出
     * @param $tpl_name
     * @param array $vars
     */
    abstract public function output($tpl_name, $vars = array());
}
/**
 * 模板类
 * @package core
 */
class Tpl
{
    /**
     * 驱动缓存
     * @var _tpl
     */
    private static $_drive = null;
    
    /**
     * 模板驱动名
     * @var string
     */
    private $_drive_name   = 'PhpTemplate';
    
    /**
     * 创建一个模板对像
     * Tpl constructor.
     * @param $template_dir
     * @param null $drive
     * @param string $temp_dir
     * @param string $cache_lifetime
     * @param string $template_filename
     */
    public function __construct($template_dir,$drive = null,$temp_dir='',$cache_lifetime='',$template_filename = '')
    {
        if(null == $drive)
        {
            $drive = $this->_drive_name;
        }
        $filename 		 = Ounun_Dir. "TplDrive/{$drive}.php";
        if(file_exists($filename))
        {
            require $filename; 
            self::$_drive = new $drive($template_dir,$temp_dir,$cache_lifetime,$template_filename);
        }
        else
       {
            trigger_error("出错:找不到{$drive}模板驱动", E_USER_ERROR);
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
     * 设定一个值
     * @param $name
     * @param null $value
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
     * 最后输出
     * @param $tpl_name
     * @param array $vars
     */
    public function output($filename,$args=array())
    {
        self::$_drive->output($filename,$args);
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
     * 导入一个模板文件
     * @param $tpl_name
     * @param array $vars
     */
    public static function import($filename,$args=array())
    {
        self::$_drive->import($filename,$args);
    }    
}
?>