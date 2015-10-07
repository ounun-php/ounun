<?php 
/** 命名空间 */
namespace ounun;
/**
 * 模板类
 * @package core
 */
class Tpl
{
    /**
     * 模板驱动名
     *
     * @var string
     */
    private $_drive_name = 'PhpTemplate';
    /**
     * 驱动缓存
     */
    private static $_drive = null;
    /**
     * 创建一个模板对像
     *
     * @param string drivee
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
    
    
    
    public function __call($name,$arg)
    {
    	if(method_exists(self::$_drive,$name))
    	{
    		self::$_drive->$name($arg[0],$arg[1]);
    	}
    	else
    	{
            trigger_error('Error: Drive not method "'.$name.'"', E_USER_ERROR);
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
     */
    public function assign($name, $value = '')
    {
        self::$_drive->assign($name, $value);
    }

    /**11 0     * Assign Template Content
     *
     * Usage Example:
     * $page->append( 'userlist',  array( 'ID' => 123,  'NAME' => 'John Doe' ) );
     * $page->append( 'userlist',  array( 'ID' => 124,  'NAME' => 'Jack Doe' ) );
     *
     * @access public
     * @param string $name Parameter Name
     * @param mixed $value Parameter Value
     * @desc Assign Template Content
     */
    public function append($name, $value)
    {
        self::$_drive->append($name, $value);
    }
    /**
     * Execute parsed Template
     * Prints Parsing Results to Standard Output
     *
     * @access public
     * @param array $_top Content Array
     * @desc Execute parsed Template
     */
    public function output($_top = '')
    {
        self::$_drive->output($_top);
    }
    
    public static function import($filename)
    {
        self::$_drive->import($filename);
    }    
}
?>