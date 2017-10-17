<?php

/** 本插件所在目录 */
define('Dir_Smarty',    realpath(__DIR__) .'/');

/** Smarty */
require_once            Dir_Smarty. 'smarty/Smarty.class.php';

/** class */
class SmartyTemplate extends \ounun\_tpl
{
    /**
     * 模板文件所以目录
     * @var string
     */
    protected $_tpl_dir;

    /**
     * @var Smarty
     */
    protected $_smarty = null;

    /**
     * 创建对像
     * @param string $template_dir
     */
    public function __construct($template_dir = null)
    {
        if($template_dir)
        {
            $this->_tpl_dir = $template_dir;
        }
        //
        $smarty = new Smarty;
        $smarty->template_dir       = $this->_tpl_dir; //模板存放位置
        $smarty->compile_dir        = Dir_Cache.'tpl_c_'.App_Name; //编译文件存放位置
        $smarty->cache_dir          = Dir_Cache.'smarty_cache_'.App_Name; //缓存文件存放位置
        $smarty->left_delimiter     = "<{";//指定左标签
        $smarty->right_delimiter    = "}>";//指定又标签
        $this->_smarty              = $smarty;
    }

    /**
     * 设定一个值
     * @param $name
     * @param null $value
     */
    public function assign($name, $value=null)
    {
        $this->_smarty->assign($name, $value);
    }

    /**
     * 追加一个 值
     * @param $name
     * @param $value
     */
    public function append($name, $value)
    {
        $this->_smarty->append($name, $value);
    }

    /**
     * 返回一个 模板文件地址(相对目录)
     * @param $tpl_name
     */
    public function file_cur($tpl_name)
    {
        trigger_error('<strong style="color:#F30">Smarty Template nonsupport file_cur:'.$tpl_name.'</strong>', E_USER_ERROR);
    }

    /**
     * 返回一个 模板文件地址(绝对目录,相对root)
     * @param $tpl_name
     */
    public function file($tpl_name)
    {
        trigger_error('<strong style="color:#F30">Smarty Template nonsupport file:'.$tpl_name.'</strong>', E_USER_ERROR);
    }

    /**
     * 导入一个模板文件
     * @param $tpl_name
     * @param array $vars
     */
    public function import($tpl_name, $vars = array())
    {
        trigger_error('<strong style="color:#F30">Smarty Template nonsupport import:'.$tpl_name.'</strong>', E_USER_ERROR);
    }

    /**
     * 最后输出
     * @param $tpl_name
     * @param array $vars
     */
    public function output($tpl_name, $vars = array())
    {
        if($vars)
        {
            $this->_smarty->assign($vars);
        }
        $this->_smarty->display($tpl_name);
    }
}