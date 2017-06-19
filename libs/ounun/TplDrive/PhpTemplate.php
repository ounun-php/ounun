<?php 
/*
 * 56.com - PHP - PhpTemplate.php
 * coding:一平
 * 创建时间:2006-11-28
 */
class PhpTemplate extends \ounun\_tpl
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
    protected $_data = array();

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
        $this->_tpl_dir_cur = null;
    }

    /**
     * 设定一个值
     * @param $name
     * @param null $value
     */
    public function assign($name, $value=null)
    {
        if(null == $value && is_array($name))
        {
            foreach ($name as $k=>$v)
            {
                $this->_data[$k] = $v;
            }
        }
        else
        {
            $this->_data[$name] = $value;
        }
    }
    /**
     * 追加一个 值
     * @param $name
     * @param $value
     */
    public function append($name, $value)
    {
        if(is_string($this->_data[$name]))
        {
            $this->_data[$name] .= $value;
        }elseif(is_array($this->_data[$name]))
        {
            $this->_data[$name][]= $value;
        }
    }

    /**
     * 返回一个 模板文件地址(相对目录)
     * @param $tpl_name
     */
    public function file_cur($tpl_name)
    {
        return $this->_tpl_dir_cur . $tpl_name;
    }

    /**
     * 返回一个 模板文件地址(绝对目录,相对root)
     * @param $tpl_name
     */
    public function file($tpl_name)
    {
        return $this->_tpl_dir . $tpl_name;
    }

    /**
     * 导入一个模板文件
     * @param $tpl_name
     * @param array $vars
     */
    public function import($tpl_name, $vars = array())
    {
        extract($this->_data);
        if($vars)
        {
            extract($vars);
        }

        $file_name    = $this->_tpl_dir_cur . $tpl_name;
        if(file_exists($file_name))
        {
            require $file_name;
        }else
        {
            $file_name = $this->_tpl_dir . $tpl_name;
            if(file_exists($file_name))
            {
                require $file_name;
            }
            else
            {
                trigger_error('<strong style="color:#F30">Can\'t find Template:'.$tpl_name.'</strong>', E_USER_ERROR);
            }
        }
    }

    /**
     * 最后输出
     * @param $tpl_name
     * @param array $vars
     */
    public function output($tpl_name, $vars = array())
    {
        extract($this->_data);
        if($vars)
        {
            extract($vars);
        }
        // ===
        $file_name 		        = $this->_tpl_dir . $tpl_name;
        if(file_exists($file_name))
        {
            $this->_tpl_dir_cur = realpath(dirname($file_name)) .'/';
            require $file_name;
        }
        else
        {
            trigger_error('<strong style="color:#F30">Can\'t find Template:'.$tpl_name.'</strong>', E_USER_ERROR);
        }
        exit();
    }
}
