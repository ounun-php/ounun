<?php
/**
 * 返回基类
 * Class Ret
 * @package ounun
 */
class ounun_ret
{
    /**
     * @var bool 返回状态
     */
    public $ret        = false;
    /**
     * @var int 错误代码
     */
    public $error_code = 0;
    /**
     * @var null 返回数据
     */
    public $data       = null;

    /**
     * Ret constructor.
     * @param $return
     * @param int $error_code
     * @param null $data
     */
    public function __construct(bool $return,int $error_code=0,$data=null)
    {
        $this->ret          = $return;
        $this->error_code   = $error_code;
        $this->data         = $data;
    }
}

/**
 * 基类的基类
 * Class Base
 * @package ounun
 */
class ounun_base
{
	/**
	 * 没定的方法
	 * @param string $method
	 * @param String $arg
	 */
	public function __call($method, $args)
	{
		header('HTTP/1.1 404 Not Found');
        $this->debug = new \debug(ounun_scfg::$dir_root.'logs/error_404_'.date('Ymd').'.txt',false,false,false,true);
        ounun::error404("base \$method:{$method} \$args:[".implode(',',$args[0])."]");
	}

	/**
	 * DB 相关
     * @var array
     */
	private static $_db = [];

	/**
	 * 返回数据库连接对像
	 * @param  string $key
	 * @return \ounun\mysqli
	 */
	public static function db(string $key,$db_cfg = null):\ounun\mysqli
	{
	    if(null == $db_cfg)
        {
            $db_cfg = $GLOBALS['_scfg']['db'][$key];
        }
		self::$_db[$key] || self::$_db[$key] = new \ounun\mysqli($db_cfg);
		// self::$_db[$key]->active();
		return self::$_db[$key];
	}
}

/**
 * 构造模块基类
 * Class ViewBase
 * @package ounun
 */
class ounun_view extends ounun_base
{
    /**
     * ounun_view constructor.
     * @param $mod
     */
	public function __construct($mod)
	{
        if(!$mod)
		{
			$mod = [ounun_scfg::def_met];
		}
        $method  = $mod[0];
		$this->$method( $mod );
	}

    /**
     * 默认 首页
     * @param array $mod
     */
    public function index($mod)
    {
        \ounun::error404();
    }

    /**
     * 默认 robots.txt文件
     * @param array $mod
     */
    public function robots($mod)
    {
        \ounun::url_check('/robots.txt');
        header('Content-Type: text/plain');
        if(file_exists(\ounun_scfg::$dir_root_app.'robots.txt'))
        {
            readfile(\ounun_scfg::$dir_root_app.'robots.txt');
        }else
        {
            exit("User-agent: *\nDisallow:");
        }
    }


    /** @var int html_cache_time */
    protected $_html_cache_time = 2678400; // 31天

    /** @var bool html_trim  */
    protected $_html_trim       = true;

    /** @var string 当前面页(网址) */
    protected $_page_url        = '';

    /** @var string 当前面页(文件名) */
    protected $_page_file       = '';

    /** 初始化Page */
    public function init_page(string $page_file = '',bool $is_cache = true,bool $is_replace = true,bool $ext_req = true,string $domain = '',int $html_cache_time = 0,bool $trim = true)
    {
        $this->_html_trim        = $trim;
        $this->_page_file        = $page_file;
        $this->_page_url         = \ounun_scfg::url_page($this->_page_file);

        if($this->_page_url)
        {
            \ounun::url_check($this->_page_url,$ext_req,$domain);
        }
        if($is_cache)
        {
            if($html_cache_time > 0)
            {
                $this->_html_cache_time = $html_cache_time;
            }
            $this->html_cache($this->_page_url);
        }

        $this->init($this->_page_url,$is_cache,$is_replace);
    }
    /** 初始化 */
    public function init(string $url = '',bool $is_cache = true,bool $is_replace = true){ }

    /**
     *  Template句柄容器
	 *  @var \tpl\template
     **/
	protected static $_stpl = null;

    /** 初始化HTMl模板类 */
	public function template($style_name = '',$style_name_default='',$dir_root='')
	{
		if(null == self::$_stpl)
        {
            $dir_root     = $dir_root  ?$dir_root :\ounun_scfg::$dir_root_app . 'template/';
            self::$_stpl  = new \tpl\template($dir_root,$style_name,$style_name_default);
        }
	}

    /** @var \ounun\html */
    protected static $_html_cache;

    /** Cache */
    public function html_cache($key) { }

    /** @param bool $output 是否马上输出cache */
    public function html_cache_stop(bool $output)
    {
        if(self::$_html_cache)
        {
            self::$_html_cache->stop($output);
        }
    }

    /**
     * html 替换数组
     * @var array
     */
    protected $_replace_data     = [];

    /**
     * @param array $data
     */
    public function replace_sets(array $data)
    {
        foreach ($data as $key => $val)
        {
            $this->_replace_data[$key] = $val;
        }
    }

    /**
     * 调试 相关
     * @var \debug
     */
    public $debug	= null;

    /**
     * 调试日志
     * @param $k
     * @param $log
     */
    public function debug_logs(string $k,$log)
    {
        if($this->debug)
        {
            $this->debug->logs($k,$log);
        }
    }

    /**
     * 停止 调试
     */
    public function debug_stop()
    {
        if($this->debug)
        {
            $this->debug->stop();
        }
    }
    
    /**
     * 返回一个 返回一个 模板文件地址(兼容)
     * @param $tpl_name
     */
    public function require_file(string $filename):string
    {
        // return $this->_stpl->file_require($filename);
        // return self::$_stpl->file_fixed_comp($filename);
        return self::$_stpl->file_require($filename);
    }

    /**
     * (兼容)返回一个 模板文件地址(绝对目录,相对root)
     * @param $filename
     */
    static public function require_fixed_comp(string $filename):string
    {
        return self::$_stpl->file_fixed_comp($filename);
    }

    /**
     * (兼容)返回一个 模板文件地址(相对目录)
     * @param $filename
     */
    static public function require_cur_comp(string $filename):string
    {
        return self::$_stpl->file_cur_comp($filename);
    }
}

/**
 * 路由
 * @param $routes      array  目录路由表
 * @param $host        string 主机
 * @param $mod         array  目录数组
 * @param $default_app string 默认应用
 * @return string 应用
 */

class ounun_scfg
{
    /** @var string Ounun目录 */
    const root_dir =  __DIR__.'/';

    /** @var string 默认模块名称 */
    const def_mod  = 'system';

    /** @var string 默认操作名称 */
    const def_met  = 'index';

    /** @var string 根目录 */
    static public $dir_root      = '';

    /** @var string App根目录 */
    static public $dir_root_app  = '';

    /** @var string 当前APP */
    static public $app           = '';

    /** @var string 当前APP Url */
    static public $app_url       = '';

    /** @var string 当前APP Host */
    static public $app_host      = '';

    /** @var string 模板 */
    static public $tpl           = '';

    /** @var string 模板(默认) */
    static public $tpl_default   = '';


    /** @var \cfg\i18n 语言包 */
    static public $i18n;

    /** @var \app\i18n 语言包 */
    static public $i18n_app;

    /** @var string 当前语言 */
    static public $lang         = 'en';

    /** @var string 默认语言 */
    static public $lang_default = 'en';


    /** @return \cfg\i18n 语言包 */
    static public function i18n()
    {
        return self::$i18n;
    }

    /** @return \app\i18n 语言包 */
    static public function i18n_app()
    {
        return self::$i18n_app;
    }

    /** @var string 当前面页 Url */
    static public function url_page(string $url='',$lang='')
    {
        if(!$lang)
        {
            $lang = self::$lang;
        }
        if($url !== '' && $url[0] == '/')
        {
            if($lang == self::$lang_default)
            {
                return self::$app_url.substr($url,1);
            }
            return '/'.$lang.self::$app_url.substr($url,1);
        }else
        {
            if($lang == self::$lang_default)
            {
                return self::$app_url.$url;
            }
            return '/'.$lang.self::$app_url.$url;
        }
    }

    /**
     * 静态地址
     * @param string|array $url
     * @param string       $pre_str
     * @param bool         $static_root
     * @return string
     */
    static public function surl($url,string $static_root = '/static/'):string
    {
        if($url && is_array($url) )
        {
            $url = count($url) > 1 ? '??'.implode(',',$url) : $url[0];
        }
        return "{$static_root}{$url}";
    }

    /**
     * 自动加载的类
     * @param $class_name
     */
    public static function autoload($class_name)
    {
        $class_name = ltrim($class_name, '\\');
        $lists 	    = explode('\\', $class_name);
        if('app' == $lists[0] && self::$dir_root_app)
        {
            array_shift($lists);
            $file_name    = implode('/', $lists).'.class.php';
            $file_name    = self::$dir_root_app  . 'libs/' . $file_name;
            if(file_exists($file_name))
            {
                require $file_name;
            }
        }else
        {
            $file_name0   = implode('/', $lists) . '.class.php';
            $file_name    = self::$dir_root_app  . 'libs/' . $file_name0;
            if (file_exists($file_name))
            {
                require $file_name;
            }else
            {
                $file_name     = self::$dir_root . 'proj.libs/' . $file_name0;
                if (file_exists($file_name))
                {
                    require $file_name;
                }else
                {
                    $file_name = self::root_dir . $file_name0;
                    if (file_exists($file_name))
                    {
                        require $file_name;
                    } // end ounun
                } // end proj.libs
            } // end app.libs
        }
    }

    /**
     * @param $lang
     * @param string $default
     */
    public static function lang_seting($lang)
    {
        self::$lang = $lang;
        if($lang  == self::$lang_default)
        {
            self::$i18n     = "\\cfg\\i18n";
            self::$i18n_app = "\\app\\i18n";
        }else
        {
            self::$i18n     = "\\cfg\\i18n\\{$lang}";
            self::$i18n_app = "\\app\\i18n\\{$lang}";
        }
    }

    /**
     * 配制文件
     * @param string $host
     * @param array $mod
     */
    public function __construct(array $mod,string $host,string $dir_root,string $dir_libs, string $lang_default,string $lang)
    {
        if($mod && $mod[0] && $this->routes["{$host}/{$mod[0]}"])
        {
            $mod_0 = array_shift($mod);
            $val_0 = $this->routes["{$host}/{$mod_0}"];
        }elseif($this->routes[$host])
        {
            $val_0 = $this->routes[$host];
        }else
        {
            $val_0 = $this->routes_default;
        }
        self::$app           = $val_0['app'];
        self::$app_url       = $val_0['url'];

        /** 默认语言 */
        self::$lang_default  = $lang_default;
        self::lang_seting($lang);

        /**   根目录 */
        self::$dir_root      = $dir_root;
        /** 应用目录 */
        self::$dir_root_app  = $dir_root.'app.'.self::$app.'/';
        /** @var string 模板 */
        self::$tpl           = $val_0['tpl']?$val_0['tpl']:self::i18n()::tpl;
        self::$tpl_default   = $val_0['tpl_default']?$val_0['tpl_default']:self::i18n()::tpl_default;

        $this->mod = $mod;
    }

    /** 路由数据 */
    protected $routes = [
        //         'www.866bet.com/api'  => ['app'=>'api', 'cls'=> 'site' ],         /* 数据接口 */
        //                 '138.vc/api'  => ['app'=>'api', 'cls'=> 'site' ],         /* 数据接口 */
        //        'www2.866bet.com/api'  => ['app'=>'api', 'cls'=> 'site' ],         /* 数据接口 */
    ];

    /** 路由数据(默认) */
    protected $routes_default = ['app'=>'www', 'url' => '/'];

    /** 路由模块数据  */
    public $mod               = [];
}


class ounun
{
    /**
     * 世界从这里开始(路由)
     * @param string $host
     * @param array $mod
     */
    public function __construct(ounun_scfg &$scfg)
    {
        /** 重定义头 */
        header('X-Powered-By: Ounun.org');

        $mod  = $scfg->mod;
        /** 加载libs/scfg.{self::$app}.ini.php文件 */
        $filename   = ounun_scfg::$dir_root_app . 'libs/scfg.'.ounun_scfg::$app.'.ini.php';
        if(file_exists($filename))
        {
            require $filename;
        }

        // 设定 模块与方法
        if(is_array($mod) && $mod[0])
        {
            $filename         = ounun_scfg::$dir_root_app . "module/{$mod[0]}.class.php";
            if(file_exists($filename))
            {
                $module		  = $mod[0];
                if($mod[1])
                {
                    array_shift($mod);
                }else
                {
                    $mod	  = [ounun_scfg::def_met];
                }
            }
            else
            {
                if($mod[1])
                {
                    $filename           = ounun_scfg::$dir_root_app . "module/{$mod[0]}/{$mod[1]}.class.php";
                    if(file_exists($filename))
                    {
                        $module		    = $mod[0].'\\'.$mod[1];
                        if($mod[2])
                        {
                            array_shift($mod);
                            array_shift($mod);
                        }else
                        {
                            $mod	    = [ounun_scfg::def_met];
                        }
                    }else
                    {
                        $filename       = ounun_scfg::$dir_root_app . "module/{$mod[0]}/system.class.php";
                        if(file_exists(\ounun_scfg::$dir_root_app . "module/{$mod[0]}" ) && file_exists($filename))
                        {
                            $module	    = "{$mod[0]}\\system";
                            array_shift($mod);
                        }else
                        {
                            $module		= ounun_scfg::def_mod;
                            $filename 	= ounun_scfg::$dir_root_app . "module/system.class.php";
                        }
                    }
                }else
                {
                    $filename       = ounun_scfg::$dir_root_app . "module/{$mod[0]}/system.class.php";
                    if(file_exists($filename))
                    {
                        $module		= "{$mod[0]}\\system";
                        $mod	    =  [ounun_scfg::def_met];
                        // array_shift($mod);
                    }else
                    {
                        // 默认模块
                        // $mod	    = array(Ounun_Default_Method);
                        $module		= ounun_scfg::def_mod;
                        $filename 	= ounun_scfg::$dir_root_app . "module/system.class.php";
                    }
                }
            } // end \Dir_App . "module/" . $mod[0] . '.class.php';
        }
        else
        {
            // 默认模块 与 默认方法
            $mod				= [ounun_scfg::def_met];
            $module				=  ounun_scfg::def_mod;
            $filename 			=  ounun_scfg::$dir_root_app . "module/system.class.php";
        }
        // 包括模块文件
        require $filename;
        // 初始化类
        $module  				= "\\module\\{$module}";
        if(class_exists($module,false))
        {
            new $module($mod);
        }
        else
        {
            header('HTTP/1.1 404 Not Found');
            trigger_error("ERROR! Can't find Module:'{$module}'.", E_USER_ERROR);
        }
    }

    /**
     * 得到访客的IP
     * @return string IP
     */
    static public function ip():string
    {
        if(isset($_SERVER['HTTP_CLIENT_IP']))
        {
            $hdr_ip = stripslashes($_SERVER['HTTP_CLIENT_IP']);
        }
        else
        {
            if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
            {
                $hdr_ip = stripslashes($_SERVER['HTTP_X_FORWARDED_FOR']);
            }
            else
            {
                $hdr_ip = stripslashes($_SERVER['REMOTE_ADDR']);
            }
        }
        return $hdr_ip;
    }

    /**
     * 输出带参数的URL
     * @param string $url   URL
     * @param array  $data  数据
     * @return string URL
     */
    static public function url(string $url,array $data,array $exts=[],array $skip=[]):string
    {
        $rs = [];
        if(is_array($data))
        {
            if($exts && is_array($exts))
            {
                foreach ($exts as $key => $value)
                {
                    $data[$key] = $value;
                }
            }
            if($skip && is_array($skip))
            {
                foreach ($skip as $key=>$value)
                {
                    if($value)
                    {
                        if(is_array($value) && in_array($data[$key],$value,true))
                        {
                            unset($data[$key]);
                        }elseif($value == $data[$key])
                        {
                            unset($data[$key]);
                        }
                    }else
                    {
                        unset($data[$key]);
                    }
                }
            }
            $rs      = [];
            $rs_page = '';
            foreach ($data as $key => $value)
            {
                if('{page}' === $value )
                {
                    $rs_page = $key . '={page}';
                }elseif(is_array($value))
                {
                    foreach ($value as $k2 => $v2)
                    {
                        $rs[] = $key.'['.$k2.']='.urlencode($v2);
                    }
                }elseif($value || 0 === $value || '0' === $value )
                {
                    $rs[] = $key.'='.urlencode($value);
                }
            }
            // 已保正page 是最后项
            if($rs_page)
            {
                $rs[] = $rs_page;
            }
        }
        $url  = trim($url);
        if($rs)
        {
            if($url && strlen($url) > 1 )
            {
                if (strpos($url, '?') === false)
                {
                    return $url.'?'.implode('&',$rs);
                }
                return $url.'&'.implode('&',$rs);
            }
            return implode('&',$rs);
        }
        return $url;
    }

    /**
     * 得到 原生 URL(去问号后的 QUERY_STRING)
     * @param $uri
     * @return string URL
     */
    static public function url_original(string $uri =''):string
    {
        if('' == $uri)
        {
            $uri = $_SERVER['REQUEST_URI'];
        }
        $tmp = explode('?', $uri, 2);
        return $tmp[0];
    }

    /**
     * 通过uri得到mod
     * @param $uri
     * @param string $root
     * @return array
     */
    static public function url_to_mod(string $uri):array
    {
        $uri 	= \explode('/',     $uri, 					2);
        $uri 	= \explode('.', 	 urldecode($uri[1]),	2);
        $uri	= \explode('/', 	 $uri[0]  );
        $mod	= [];
        foreach ($uri as $v)
        {
            $v !== '' && $mod[] = $v;
        }
        return $mod;
    }

    /**
     * URL去重
     * @param string $url_original      网址
     * @param bool $ext_req             网址可否带参加数
     * @param null $domain              是否捡查 域名
     */
    static public function url_check(string $url_original = "",bool $ext_req = true,string $domain = '')
    {
        // URL去重
        $url        = explode('?',$_SERVER['REQUEST_URI'],2);
        $url_reset  = '';
        if(false == $ext_req && $url[1])
        {
            $url_reset  = $url_original;
        }elseif($url_original != $url[0])
        {
            $url_reset  = $url_original;
            if($ext_req && $url[1])
            {
                $url_reset  = "{$url_reset}?{$url[1]}";
            }
        }
        // echo("\$url_reset:{$url_reset} \$url_original:{$url_original}\n");
        // exit("\$domain:{$domain}\n");
        // 域名
        if($domain && $domain != $_SERVER['HTTP_HOST'])
        {
            $domain     = $_SERVER['HTTP_HOST'];
            $url_reset  = $url_reset?$url_reset:$_SERVER['REQUEST_URI'];
            $url_reset  = "//{$domain}{$url_reset}";
            // exit("\$url_reset:{$url_reset} \$domain:{$domain}\n");
            self::go_url($url_reset,false,301);
        }else if($url_reset)
        {
            // exit("\$url_reset:{$url_reset}\n");
            self::go_url($url_reset,false,301);
        }
        // exit("\$domain:{$domain}\n");
    }

    /**
     * @param string $url1
     * @param string $url2
     * @param string $note
     * @param bool $top
     */
    static public function go_note(string $url1,string $url2,string $note,bool $top=false):void
    {
        $top  = "\t" . ($top?'window.top.':'');
        $note = $note?$note:'点击“确定”继续操作  点击“取消” 中止操作';
        echo '<script type="text/javascript">' . "\n";
        if($url2)
        {
            $url1 = $top . "location.href='{$url1}';\n" ;
            $url2 = $top . "location.href='{$url2}';\n" ;
            echo 'if(window.confirm(' . json_encode($note) . ')){' . "\n" . $url1 . '}else{' . "\n" . $url2. '}' . "\n";
        }
        else
        {
            $url1 = $top . "location.href='{$url1}';\n" ;
            echo 'if(window.confirm(' . json_encode($note) . ')){' . "\n" . $url1 . '};'. "\n";
        }
        echo '</script>' . "\n";
        exit();
    }

    /**
     * @param $url
     * @param bool $top
     * @param int $head_code
     * @param int $delay      延时跳转(单位秒)
     */
    static public function go_url(string $url,bool $top=false,int $head_code=302,int $delay=0):void
    {
        if($top)
        {
            echo '<script type="text/javascript">' . "\n";
            echo "window.top.location.href='{$url}';\n";
            echo '</script>' . "\n";
        }
        else
        {
            if(!headers_sent() && 0 == $delay)
            {
                header('Location: '.$url,null,$head_code);
            }
            else
            {
                echo '<meta http-equiv="refresh" content="'.((int)$delay).';url=' . $url . '">';
            }
        }
        exit();
    }

    /**
     * 返回
     */
    static public function go_back():void
    {
        echo '<script type="text/javascript">',"\n",
        'window.history.go(-1);',"\n",
        '</script>',"\n";
        exit();
    }

    /**
     * @param $msg
     * @param $url
     */
    static public function go_msg(string $msg,string $url = ''):void
    {
        if($url)
        {
            exit(self::msg($msg).'<meta http-equiv="refresh" content="0.5;url=' . $url . '">');
        }else
        {
            echo self::msg($msg);
            self::go_back();
        }
    }


    /**
     * 获得 json字符串数据
     * @param $data
     * @return string
     */
    static public function json_encode($data):string
    {
        return \json_encode($data,JSON_UNESCAPED_UNICODE);
    }

    /**
     * 对 json格式的字符串进行解码
     * @param string $json_string
     * @return mixed
     */
    static public function json_decode(string $json_string)
    {
        return \json_decode($json_string,true);
    }

    /**
     * 获得 exts数据php
     * @param string $exts_string
     * @return array|mixed
     */
    static public function exts_decode_php(string $exts_string)
    {
        $exts     = [];
        if($exts_string)
        {
            $exts = unserialize($exts_string);
        }
        return $exts;
    }

    /**
     * 获得 exts数据json
     * @param string $exts_string
     * @return array|mixed
     */
    static public function exts_decode_json(string $exts_string)
    {
        $exts     = [];
        if($exts_string)
        {
            $exts = json_decode($exts_string);
        }
        return $exts;
    }

    /**
     * 对字符串进行编码，这样可以安全地通过URL
     *
     * @param string $string to encode
     * @return string
     */
    static public function base64_url_encode(string $string = null):string
    {
        return strtr(base64_encode($string), '+/=', '-_~');
    }

    /**
     * 解码一个 URL传递的字符串
     *
     * @param string $string to decode
     * @return string
     */
    static public function base64_url_decode(string $string = null):string
    {
        return base64_decode(strtr($string, '-_~', '+/='));
    }

    /**
     * 编号 转 字符串
     *
     * @param string $string to encode
     * @return string
     */
    static public function short_url_encode(int $id = 0):string
    {
        if($id < 10)
        {
            return (string)$id;
        }
        $show = '';
        while($id>0)
        {
            $s    = $id % 62;
            $show = ($s>35
                    ? chr($s+61)
                    : ($s>9
                        ? chr($s+55)
                        : $s
                    )).$show;
            $id    = floor($id/62);
        }
        return $show;
    }

    /**
     * 字符串 转 编号
     *
     * @param string $string 字符串
     * @return int
     */
    static public function short_url_decode(string $string = ''):int
    {
        $p  = 0;
        while($string !== '')
        {
            $s      = substr($string,0,1);
            $n      = is_numeric($s)?$s:ord($s);
            $p      = $p*62 + (($n >= 97)?( $n - 61) :( $n >= 65 ? $n - 55 : $n )) ;
            $string = substr($string,1);
        }
        return $p;
    }

    /**
     * 彈出對話框
     *
     * @param string $msg
     * @param boolean $outer
     * @return string
     */
    static public function msg(string $msg, bool $outer = true, $meta = true):string
    {
        $rs = "\n" . 'alert(' . json_encode($msg) . ');' . "\n";
        if($outer)
        {
            if($meta)
            {
                $mt = '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />' . "\n";
            }else
            {
                $mt = '';
            }
            $rs = $mt. '<script type="text/javascript">' . "\n"
                . $rs . "\n"
                . '</script>' . "\n";
        }
        return $rs;
    }

    /**
     * 出错提示错
     *
     * @param string $msg
     * @param bool $close
     */
    static public function msg_close(string $msg,bool $close=false):void
    {
        $rs = "\n" . 'alert(' . json_encode($msg) . ');' . "\n";
        $mt = '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />' . "\n";
        $rs = $mt. '<script type="text/javascript">' . "\n"
            . $rs . "\n"
            . '</script>' . "\n";
        echo $rs;
        if($close)
        {
            // 本页自动关闭.
            echo '<script type="text/javascript">window.opener = null; window.open("", "_self", ""); window.close(); </script>';
        }
        exit();
    }

    /**
     * 获得libs Data数据
     * @param string $data_mod
     * @param string $data_dir
     * @return mixed|null
     */
    static public function data(string $data_mod,string $data_dir)
    {
        $filename  = "{$data_dir}data.{$data_mod}.ini.php";
        if(file_exists($filename))
        {
            return require $filename;
        }
        return null;
    }

    /**
     * HTTP缓存控制
     *
     * @param int 		$expires		缓存时间 0:为不缓存 单位:s
     * @param string 	$etag			ETag
     * @param int 		$LastModified	最后更新时间
     */
    static public function expires(int $expires = 0,string $etag = '', int $LastModified = 0)
    {
        if($expires)
        {
            $time   = time();
            header("Expires: " . gmdate("D, d M Y H:i:s", $time + $expires) . " GMT");
            header("Cache-Control: max-age=" . $expires);
            $LastModified && header("Last-Modified: " . gmdate("D, d M Y H:i:s", $LastModified) . " GMT");
            if($etag)
            {
                if($etag == $_SERVER["HTTP_IF_NONE_MATCH"])
                {
                    header("Etag: " . $etag, true, 304);
                    exit();
                }
                else
                {
                    header("Etag: " . $etag);
                }
            }
        }
        else
        {
            header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
            header("Cache-Control: no-cache, must-revalidate");
            header("Pragma: no-cache");
        }
    }

    /**
     * error 404
     */
    static public function error404($msg=''):void
    {
        header('HTTP/1.1 404 Not Found');
        if(function_exists('\error404'))
        {
            \error404();
        }
        exit('<html>
            <head>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
                <title>404 Not Found</title>
            </head>
            <body bgcolor="white">
                <center>
                    <h1>404 Not Found'.($msg?'('.$msg.')':'').'</h1>
                </center>
                <hr>
                <center><a href="/">返回网站首页</a></center>
            </body>
            </html>
            <!-- a padding to disable MSIE and Chrome friendly error page -->
            <!-- a padding to disable MSIE and Chrome friendly error page -->
            <!-- a padding to disable MSIE and Chrome friendly error page -->
            <!-- a padding to disable MSIE and Chrome friendly error page -->
            <!-- a padding to disable MSIE and Chrome friendly error page -->
            <!-- a padding to disable MSIE and Chrome friendly error page -->');
    }

    /**
     * @param $delimiters
     * @param $string
     * @return array
     */
    static public function explodes(string $delimiters,string $string)
    {
        $ready  = \str_replace($delimiters, $delimiters[0], $string);
        $launch = \explode($delimiters[0],  $ready);
        return $launch;
    }

    /**
     * Convert special characters to HTML safe entities.
     * 特殊字符转换成 HTML安全格式。
     *
     * @param string $string to encode
     * @return string
     */
    static public function safe(string $string):string
    {
        return htmlspecialchars($string, ENT_QUOTES, 'utf-8');
    }

    /**
     * Filter a valid UTF-8 string so that it contains only words, numbers,
     * dashes, underscores, periods, and spaces - all of which are safe
     * characters to use in file names, URI, XML, JSON, and (X)HTML.
     *
     * @param string $string to clean
     * @param bool $spaces TRUE to allow spaces
     * @return string
     */
    static public function sanitize(string $string, bool $spaces = true):string
    {
        $search = [
            '/[^\w\-\. ]+/u',			// Remove non safe characters
            '/\s\s+/',					// Remove extra whitespace
            '/\.\.+/', '/--+/', '/__+/'	// Remove duplicate symbols
        ];

        $string = preg_replace($search, [' ', ' ', '.', '-', '_'], $string);

        if( ! $spaces)
        {
            $string = preg_replace('/--+/', '-', str_replace(' ', '-', $string));
        }
        return trim($string, '-._ ');
    }

    /**
     * Create a SEO friendly URL string from a valid UTF-8 string.
     *
     * @param string $string to filter
     * @return string
     */
    static public function sanitize_url(string $string):string
    {
        return urlencode(mb_strtolower(self::sanitize($string, false)));
    }

    /**
     * Filter a valid UTF-8 string to be file name safe.
     *
     * @param string $string to filter
     * @return string
     */
    static public function sanitize_filename(string $string):string
    {
        return self::sanitize($string, false);
    }
}

/** 注册自动加载 */
spl_autoload_register('\\ounun_scfg::autoload');