<?php
/**
 * Created by PhpStorm.
 * User: dreamxyp
 * Date: 2018/5/7
 * Time: 15:32
 */

namespace tpl;


class template
{
    /**
     * 模板根目录
     * @var string
     */
    protected $_dir_root;

    /**
     * 模板目录(当前)
     * @var string
     */
    protected $_dir_current;
    /**
     * 模板样式(当前)
     * @var string
     */
    protected $_style_current;

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
     * 创建对像
     * @param string $dir_root
     */
    public function __construct($dir_tpl_root = '', $style_name = '', $style_name_default = '')
    {
        if($dir_tpl_root)
        {
            $this->_dir_root            = $dir_tpl_root;
        }
        if($style_name)
        {
            $this->_style_name          = $style_name;
        }
        if($style_name_default)
        {
            $this->_style_name_default  = $style_name_default;
        }
        $this->_dir_current             = '';
        $this->_style_current           = '';
    }



    /**
     * 返回一个 模板文件地址(绝对目录,相对root)
     * @param $filename
     */
    public function file_fixed(string $filename):string
    {
        return "{$this->_dir_root}{$this->_style_name}/{$filename}";
    }

    /**
     * (兼容)返回一个 模板文件地址(绝对目录,相对root)
     * @param $filename
     */
    public function file_fixed_comp(string $filename):string
    {
        $filename2 = "{$this->_dir_root}{$this->_style_name}/{$filename}";
        if(file_exists($filename2))
        {
            return $filename2;
        }
        return "{$this->_dir_root}{$this->_style_name_default}/{$filename}";
    }

    /**
     * 返回一个 模板文件地址(相对目录)
     * @param $filename
     */
    public function file_cur(string $filename):string
    {
        return "{$this->_dir_root}{$this->_style_current}/{$this->_dir_current}{$filename}";
    }

    /**
     * (兼容)返回一个 模板文件地址(相对目录)
     * @param $filename
     */
    public function file_cur_comp(string $filename):string
    {
        if($this->_style_current)
        {
            $filename2 = "{$this->_dir_root}{$this->_style_current}/{$this->_dir_current}{$filename}";
            if(file_exists($filename2))
            {
                return $filename2;
            }
            if($this->_style_name == $this->_style_current)
            {
                return "{$this->_dir_root}{$this->_style_name_default}/{$this->_dir_current}{$filename}";
            }else
            {
                return "{$this->_dir_root}{$this->_style_name}/{$this->_dir_current}{$filename}";
            }
        }else{
            $filename2 = "{$this->_dir_root}{$this->_style_name}/{$this->_dir_current}{$filename}";
            if(file_exists($filename2))
            {
                return $filename2;
            }
            return "{$this->_dir_root}{$this->_style_name_default}/{$this->_dir_current}{$filename}";
        }
    }

    /**
     * 返回一个 模板文件地址(兼容)
     * @param $filename
     */
    public function file_require(string $filename)
    {
        // 相对
        if($this->_style_current)
        {
            $filename2     = "{$this->_dir_root}{$this->_style_current}/{$this->_dir_current}{$filename}";
            if(file_exists($filename2))
            {
                return $filename2;
            }
            if($this->_style_name == $this->_style_current)
            {
                return "{$this->_dir_root}{$this->_style_name_default}/{$this->_dir_current}{$filename}";
            }else
            {
                return "{$this->_dir_root}{$this->_style_name}/{$this->_dir_current}{$filename}";
            }
        }
        // 绝对
        $filename2     = "{$this->_dir_root}{$this->_style_name}/{$filename}";
        if( file_exists($filename2) )
        {
            $current                  = dirname($filename);
            if('.' == $current || '' == $current || '/' == $current){
                $this->_dir_current   = '';
                $this->_style_current = $this->_style_name;
            }else{
                $this->_dir_current   = $current.'/';
                $this->_style_current = $this->_style_name;
            }
            return $filename2;
        }else
        {
            $filename2 = "{$this->_dir_root}{$this->_style_name_default}/{$filename}";
            if( file_exists($filename2) )
            {
                $current            = dirname($filename);
                if('.' == $current || '' == $current || '/' == $current){
                    $this->_dir_current   = '';
                    $this->_style_current = $this->_style_name_default;
                }else{
                    $this->_dir_current   = $current.'/';
                    $this->_style_current = $this->_style_name_default;
                }
                return $filename2;
            }
        }
        trigger_error("Can't find Template:{$filename2} \nstyle:{$this->_style_name} \nstyle_default:{$this->_style_name_default}", E_USER_ERROR);
    }



    protected  $_replace_data = [];

    protected  $_trim         = false;

    /**
     * 替换
     */
    public function replace(array $data = [],bool $trim = true)
    {
        $this->_replace_data = $data;
        $this->_trim         = $trim;
        ob_start();
        register_shutdown_function([$this,'callback'],false);
    }


    /**
     * 创建缓存
     * @param $output 是否有输出
     */
    public function callback(bool $output)
    {
        // 执行
        $buffer     = ob_get_contents();
        ob_clean();
        ob_implicit_flush(1);

        // 写文件
        if($this->_trim)
        {
            $pattern     = ['/<!--.*?-->/','/[^:\-\"]\/\/[^\S].*?\n/', '/\/\*.*?\*\//', '/[\n\r\t]*?/', '/\s{2,}/','/>\s?</','/<!--.*?-->/','/\"\s?>/'];
            $replacement = [''            ,''                        , ''             , ''            , ' '       ,'><'     ,''            ,'">'];
            $buffer      = preg_replace($pattern,$replacement,$buffer);
        }
        if($this->_replace_data)
        {
            $val    = array_values($this->_replace_data);
            $key    = array_keys($this->_replace_data);

//            print_r($val);
//            print_r($key);
            $buffer = str_replace($key,$val,$buffer);
        }
//        $buffer     = gzencode($buffer, 9);
//        header('Content-Encoding: gzip');
//        header('Content-Length: '. strlen($buffer));
        exit($buffer);
    }
}