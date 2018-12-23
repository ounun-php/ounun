<?php
namespace ounun;


class template
{
    /** @var string 模板目录(当前) */
    protected $_dir_current;

    /** @var string 模板样式(当前) */
    protected $_style_current;

    /** @var string 模板样式目录 */
    protected $_style_name;

    /** @var string 模板文件所以目录(默认) */
    protected $_style_name_default;

    /** @var bool 是否去空格 换行 */
    protected $_is_trim    = false;

    /**
     * 创建对像 template constructor.
     * @param string $style_name           模板根目录
     * @param string $style_name_default   模板文件所以目录(默认)
     * @param bool $is_trim
     */
    public function __construct(string $style_name = '', string $style_name_default = '', bool $is_trim = false)
    {
        $style_name         && $this->_style_name          = $style_name;
        $style_name_default && $this->_style_name_default  = $style_name_default;

        $this->_dir_current     = '';
        $this->_style_current   = '';
        $this->_is_trim         = $is_trim;
        // if($this->_is_trim)
        // {
        //     trigger_error("Template".':'.($this->_is_trim?'1':'0'), E_USER_ERROR);
        //     exit(__FILE__.':'.($this->_is_trim?'1':'0'));
        // }
        $this->replace();
    }

    /**
     * (兼容)返回一个 模板文件地址(绝对目录,相对root)
     * @param string $filename
     * @param array $styles
     * @return string
     */
    public function tpl_fixed(string $filename,array $styles = []):string
    {
        $styles = $styles ? $styles : [$this->_style_name,$this->_style_name_default];
        foreach (scfg::$tpl_dirs as $dir)
        {
            foreach ($styles as $style)
            {
                $filename2 = "{$dir}{$style}/{$filename}";
                if(file_exists($filename2))
                {
                    $this->_dir_current   = dirname($filename2).'/';
                    $this->_style_current = $style;
                    return $filename2;
                }
            }
        }
        $this->error($filename);
    }

    /**
     * (兼容)返回一个 模板文件地址(相对目录)
     * @param string $filename
     * @return string
     */
    public function tpl_curr(string $filename):string
    {
        // curr
        if($this->_dir_current)
        {
            $filename2 = "{$this->_dir_current}{$filename}";
            if(file_exists($filename2))
            {
                return $filename2;
            }
        }

        // fixed
        if($this->_style_current)
        {
            if($this->_style_current == $this->_style_name_default)
            {
                $styles = [$this->_style_name_default,$this->_style_name];
            }else
            {
                $styles = [$this->_style_name,$this->_style_name_default];
            }
        }else
        {
            $styles = [$this->_style_name,$this->_style_name_default];
        }

        return $this->tpl_fixed($filename,$styles);
    }


    /**
     * 报错
     * @param $filename
     */
    protected function error($filename)
    {
        trigger_error("Can't find Template:{$filename} \ndirs:[".implode(',',scfg::$tpl_dirs)."] \nstyle:{$this->_style_name} \nstyle_default:{$this->_style_name_default}", E_USER_ERROR);
    }

    /**
     * 替换
     * @param bool $trim
     */
    public function replace()
    {
        if(!\v::$cache_html || \v::$cache_html->stop)
        {
            ob_start();
            register_shutdown_function([$this,'callback'],false);
        }
    }

    /**
     * 创建缓存
     * @param bool $output 是否有输出
     */
    public function callback(bool $output)
    {
        // 执行
        $buffer     = ob_get_contents();
        ob_clean();
        ob_implicit_flush(1);

        // 写文件
        if($this->_is_trim)
        {
            $pattern     = ['/<!--.*?-->/','/[^:\-\"]\/\/[^\S].*?\n/', '/\/\*.*?\*\//', '/[\n\r\t]*?/', '/\s{2,}/','/>\s?</','/<!--.*?-->/','/\"\s?>/'];
            $replacement = [''            ,''                        , ''             , ''            , ' '       ,'><'     ,''            ,'">'];
            $buffer      = preg_replace($pattern,$replacement,$buffer);
        }

        // 替换
        if(scfg::$tpl_data)
        {
            scfg::$view->tpl_data_default();
            $buffer = strtr($buffer,scfg::$tpl_data);
        }

//      $buffer     = gzencode($buffer, 9);
//      header('Content-Encoding: gzip');
//      header('Content-Length: '. strlen($buffer));
        exit($buffer);
    }
}