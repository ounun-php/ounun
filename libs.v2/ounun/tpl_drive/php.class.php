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
    protected $_data = [];

    /**
     * 创建对像
     * @param string $dir_root
     */
    public function __construct($dir_root = '', $style_name = '', $style_name_default = '')
    {
        if($dir_root)
        {
            $this->_dir_root            = $dir_root;
        }
        if($style_name)
        {
            $this->_style_name          = $style_name;
        }
        if($style_name_default)
        {
            $this->_style_name_default  = $style_name;
        }
        $this->_dir_current             = '';
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
    public function append($name, $value=null)
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
     * @param $filename
     */
    public function file($filename)
    {
        return "{$this->_dir_root}{$this->_style_name}/{$filename}";
    }

    /**
     * 返回一个 模板文件地址(相对目录)
     * @param $filename
     */
    public function file_cur($filename)
    {
        return "{$this->_dir_root}{$this->_style_name}/{$this->_dir_current}{$filename}";
    }

    /**
     * 返回一个 模板文件地址(兼容)
     * @param $filename
     */
    public function file_comp($filename)
    {
        // 相对
        if($this->_dir_current)
        {
            $filename2     = "{$this->_dir_root}{$this->_style_name}/{$this->_dir_current}{$filename}";
            if(file_exists($filename2))
            {
                return $filename2;
            }else
            {
                $filename2 = "{$this->_dir_root}{$this->_style_name_default}/{$this->_dir_current}{$filename}";
                if(file_exists($filename2))
                {
                    return $filename2;
                }
            }
        }
        // 绝对
        $filename2     = "{$this->_dir_root}{$this->_style_name}/{$filename}";
        if( file_exists($filename2) )
        {
            $current            = dirname($filename);
            $this->_dir_current = ('.' == $current || '' == $current || '/' == $current ) ? '' : $current.'/';
            return $filename2;
        }else
        {
            $filename2 = "{$this->_dir_root}{$this->_style_name_default}/{$filename}";
            if( file_exists($filename2) )
            {
                $current            = dirname($filename);
                $this->_dir_current = ('.' == $current || '' == $current || '/' == $current ) ? '' : $current.'/';
                return $filename2;
            }
        }
        trigger_error("Can't find Template:{$filename2} \nstyle:{$this->_style_name} \nstyle_default:{$this->_style_name_default}", E_USER_ERROR);
    }

    /**
     * 导入一个模板文件
     * @param $filename
     * @param array $vars
     */
    public function import($filename, $vars = [])
    {
        extract($this->_data);
        if($vars)
        {
            extract($vars);
        }
        require $this->file_comp($filename);
    }

    /**
     * 最后输出
     * @param $filename
     * @param array $vars
     */
    public function output($filename, $vars = [])
    {
        $this->import($filename,$vars);
        exit();
    }
}
