<?php

namespace ounun\tpl_drive;
/*
 * 56.com - PHP - PhpTemplate.php
 * coding:一平
 * 创建时间:2006-11-28
 */
class php extends \ounun\_tpl
{
    /**
     * 模板文件所以目录
     * @var string
     */
    protected $_tpl_dir;

    /**
     * 模板文件所以目录(移动)
     * @var string
     */
    protected $_tpl_dir_backup;

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
     * @param string $tpl_dir
     */
    public function __construct($tpl_dir = null, $tpl_dir_backup = null)
    {
        if($tpl_dir)
        {
            $this->_tpl_dir = $tpl_dir;
        }
        if($tpl_dir_backup)
        {
            $this->_tpl_dir_backup = $tpl_dir_backup;
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
     * 返回一个 模板文件地址(绝对目录,相对root)
     * @param $tpl_name
     */
    public function file($tpl_name)
    {
        return $this->_tpl_dir . $tpl_name;
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
     * 返回一个 模板文件地址(兼容)
     * @param $tpl_name
     */
    public function file_comp($tpl_name)
    {
        // 相对
        if($this->_tpl_dir_cur)
        {
            $filename = $this->_tpl_dir_cur . $tpl_name;
            if(file_exists($filename))
            {
                return $filename;
            }
        }
        // 绝对
        $filename = $this->_tpl_dir . $tpl_name;
        if( file_exists($filename) )
        {
            $this->_tpl_dir_cur    = realpath(dirname($filename)) .'/';
            return $filename;
        }
        // 备份
        if($this->_tpl_dir_backup )
        {
            $filename = $this->_tpl_dir_backup . $tpl_name;
            if( file_exists($filename) )
            {
                $this->_tpl_dir_cur = realpath(dirname($filename)) .'/';
                return $filename;
            }
        }
        return null;
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

        $filename  = $this->file_comp($tpl_name);
        if($filename)
        {
            require $filename;
        }else
        {
            trigger_error('<strong style="color:#F30">Can\'t find Template:'.$tpl_name.'</strong>', E_USER_ERROR);
        }
    }

    /**
     * 最后输出
     * @param $tpl_name
     * @param array $vars
     */
    public function output($tpl_name, $vars = array())
    {
        $this->import($tpl_name,$vars);
        exit();
    }
}
