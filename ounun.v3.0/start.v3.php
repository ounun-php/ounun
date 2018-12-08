<?php
namespace ounun;

require __DIR__.'/common.php';
/**
 * 基类的基类
 * Class Base
 * @package ounun
 */
class base
{
	/**
	 * 没定的方法
	 * @param string $method
	 * @param String $arg
	 */
	public function __call($method, $args)
	{
		header('HTTP/1.1 404 Not Found');
        $this->debug = new \debug(scfg::$dir_root.'logs/error_404_'.date('Ymd').'.txt',false,false,false,true);
        error404("base \$method:{$method} \$args:[".implode(',',$args[0])."]");
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
class view extends base
{
    /**
     * ounun_view constructor.
     * @param $mod
     */
	public function __construct($mod)
	{
        if(!$mod)
		{
			$mod = [scfg::def_met];
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
        error404();
    }

    /**
     * 默认 robots.txt文件
     * @param array $mod
     */
    public function robots($mod)
    {
        url_check('/robots.txt');
        header('Content-Type: text/plain');
        if(file_exists(scfg::$dir_root_app.'robots.txt'))
        {
            readfile(scfg::$dir_root_app.'robots.txt');
        }else
        {
            exit("User-agent: *\nDisallow:");
        }
    }

    /**
     * adm2.moko8.com/favicon.ico
     */
    public function favicon($mod)
    {
        go_url(scfg::$url_static.'favicon.ico',false,301);
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
        $this->_page_url         = scfg::url_page($this->_page_file);

        if($this->_page_url)
        {
            url_check($this->_page_url,$ext_req,$domain);
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
	public function template(string $style_name = '',string $style_name_default='',string $dir_tpl_root='',string $dir_tpl_root_g = '')
	{
		if(null == self::$_stpl)
        {
            $dir_tpl_root   = $dir_tpl_root  ?$dir_tpl_root  :\ounun_scfg::$dir_root_app    . 'template/';
            $dir_tpl_root_g = $dir_tpl_root_g?$dir_tpl_root_g:dirname(\ounun_scfg::$lib_cms). '/cms.single.template.v1/';
            self::$_stpl    = new template($dir_tpl_root,$style_name,$style_name_default,$dir_tpl_root_g);
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
     * @param string $filename
     * @return string
     */
    public function require_file(string $filename):string
    {
        // return $this->_stpl->file_require($filename);
        // return self::$_stpl->file_fixed_comp($filename);
        return self::$_stpl->file_require($filename);
    }

    /**
     * 返回一个 模板文件地址(兼容)(公共)
     * @param string $filename
     * @return string
     */
    public function require_file_g(string $filename):string
    {
        return self::$_stpl->file_require_g($filename);
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

class scfg
{
    /** @var string 默认模块名称 */
    const def_mod   = 'system';
    /** @var string 默认操作名称 */
    const def_met   = 'index';

    /** @var string Ounun目录   */
    static public $lib_ounun     =  __DIR__.'/';
    /** @var string CMS目录   */
    static public $lib_cms       = '';
    /** @var string APP目录   */
    static public $lib_app       = '';

    /** @var string Www URL */
    static public $url_www       = '';
    /** @var string Mobile URL */
    static public $url_mobile    = '';
    /** @var string Mip URL */
    static public $url_mip       = '';
    /** @var string Api URL */
    static public $url_api       = '';
    /** @var string Res URL */
    static public $url_res       = '';
    /** @var string Static URL */
    static public $url_static    = '';
    /** @var string StaticG URL */
    static public $url_static_g  = '';


    /** @var string 根目录 */
    static public $dir_root      = '';
    /** @var string 根目录(App) */
    static public $dir_root_app  = '';

    /** @var string 当前APP */
    static public $app           = '';
    /** @var string 当前APP Url */
    static public $app_url       = '';
    /** @var string 域名Domain */
    static public $app_domain    = '';

    /** @var string 模板-样式 */
    static public $tpl           = '';
    /** @var string 模板-样式[默认] */
    static public $tpl_default   = '';

    /** @var \cfg\i18n 语言包 */
    static public $i18n;
    /** @var \app\i18n 语言包 */
    static public $i18n_app;

    /** @var string 当前语言 */
    static public $lang         = 'zh_CN';
    /** @var string 默认语言 */
    static public $lang_default = 'zh_CN';


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
        if('app' == $lists[0] && self::$lib_app)
        {
            array_shift($lists);
            $file_name    = implode('/', $lists).'.class.php';
            $file_name    = self::$lib_app . $file_name;
            if(file_exists($file_name))
            {
                require $file_name;
            }
        }else
        {
            $file_name0   = implode('/', $lists) . '.class.php';
            $file_name    = self::$lib_app . $file_name0;
            if (file_exists($file_name))
            {
                require $file_name;
            }else
            {
                $file_name     = self::$lib_cms . $file_name0;
                if (file_exists($file_name))
                {
                    require $file_name;
                }else
                {
                    $file_name = self::$lib_ounun . $file_name0;
                    if (file_exists($file_name))
                    {
                        require $file_name;
                    } // end ounun
                } // end proj.libs
            } // end app.libs
        }
    }

    /**
     * 配制文件
     * @param string $host
     * @param array $mod
     */
    public function __construct(array $mod,string $host,string $lang_default,string $lang,array $dirs = [], array $libs = [], array $routes = [])
    {
        if($routes)
        {
            $this->routes_data($routes['routes'],$routes['routes_default']);
        }
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
        self::langs($lang);

        /** 目录(根/应用) */
        if($dirs)
        {
            self::dirs($dirs['root'],$dirs['root_app']);
        }

        /** 库(Ounun/Cms/App) */
        if($libs)
        {
            self::libs($libs['ounun'],$libs['cms'],$libs['app']);
        }

        /** @var string 模板 */
        self::$tpl           = $val_0['tpl']?$val_0['tpl']:self::i18n()::tpl;
        self::$tpl_default   = $val_0['tpl_default']?$val_0['tpl_default']:self::i18n()::tpl_default;

        $this->mod = $mod;
    }

    /**
     * @param string $lang
     * @param string $default
     */
    public static function langs($lang)
    {
        self::$lang = $lang;
        // echo "self::\$lang:".self::$lang." \$lang_default:".self::$lang_default."\n";
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
     * @param string $lib_ounun
     * @param string $lib_cms
     * @param string $lib_app
     */
    public function libs(string $lib_ounun,string $lib_cms,string $lib_app)
    {
        // Ounun目录
        self::$lib_ounun    = $lib_ounun;
        // CMS目录
        self::$lib_cms      = $lib_cms;
        // APP目录
        if($lib_app)
        {
            self::$lib_app  = $lib_app;
        }else
        {
            self::$lib_app  = self::$dir_root_app.'libs/';
        }
    }

    /**
     * @param string $dir_root
     * @param string $dir_root_app
     */
    public function dirs(string $dir_root,string $dir_root_app = '')
    {
        /** 根目录 */
        self::$dir_root      = $dir_root;
        /** 应用目录 */
        if($dir_root_app)
        {
            self::$dir_root_app  = $dir_root_app;
        }else
        {
            self::$dir_root_app  = $dir_root.'app.'.self::$app.'/';
        }
    }

    /**
     * @param string $url_www
     * @param string $url_mobile
     * @param string $url_res
     */
    public function urls(string $url_www,string $url_mobile,string $url_mip,string $url_api,string $url_res,string $url_static,string $url_static_g,string $app_domain)
    {
        /** Www URL */
        self::$url_www       = $url_www;
        /** Mobile URL */
        self::$url_mobile    = $url_mobile;
        /** Mobile URL */
        self::$url_mip       = $url_mip;
        /** Api URL */
        self::$url_api       = $url_api;
        /** Res URL */
        self::$url_res       = $url_res;
        /** Static URL */
        self::$url_static    = $url_static;
        /** StaticG URL */
        self::$url_static_g  = $url_static_g;

        /** 项目主域名 */
        self::$app_domain    = $app_domain;
    }

    /**
     * @param array $routes
     * @param array $routes_default
     */
    public function routes_data(array $routes = [],array $routes_default = [])
    {
        if($routes)
        {
            $this->routes = $routes;
        }
        if($routes_default)
        {
            $this->routes_default = $routes_default;
        }
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


class start
{
    /**
     * 世界从这里开始(路由)
     * @param string $host
     * @param array $mod
     */
    public function __construct(scfg &$scfg)
    {
        /** 重定义头 */
        header('X-Powered-By: Ounun.org');

        $mod        = $scfg->mod;
        /** 加载libs/scfg.{self::$app}.ini.php文件 */
        $filename   = scfg::$dir_root_app . 'libs/scfg.'.scfg::$app.'.ini.php';
        if(file_exists($filename))
        {
            require $filename;
        }

        // 设定 模块与方法
        if(is_array($mod) && $mod[0])
        {
            $filename         = scfg::$dir_root_app . "module/{$mod[0]}.class.php";
            if(file_exists($filename))
            {
                $module		  = $mod[0];
                if($mod[1])
                {
                    array_shift($mod);
                }else
                {
                    $mod	  = [scfg::def_met];
                }
            }
            else
            {
                if($mod[1])
                {
                    $filename           = scfg::$dir_root_app . "module/{$mod[0]}/{$mod[1]}.class.php";
                    if(file_exists($filename))
                    {
                        $module		    = $mod[0].'\\'.$mod[1];
                        if($mod[2])
                        {
                            array_shift($mod);
                            array_shift($mod);
                        }else
                        {
                            $mod	    = [scfg::def_met];
                        }
                    }else
                    {
                        $filename       = scfg::$dir_root_app . "module/{$mod[0]}/system.class.php";
                        if(file_exists(\scfg::$dir_root_app . "module/{$mod[0]}" ) && file_exists($filename))
                        {
                            $module	    = "{$mod[0]}\\system";
                            array_shift($mod);
                        }else
                        {
                            $module		= scfg::def_mod;
                            $filename 	= scfg::$dir_root_app . "module/system.class.php";
                        }
                    }
                }else
                {
                    $filename       = scfg::$dir_root_app . "module/{$mod[0]}/system.class.php";
                    if(file_exists($filename))
                    {
                        $module		= "{$mod[0]}\\system";
                        $mod	    =  [scfg::def_met];
                        // array_shift($mod);
                    }else
                    {
                        // 默认模块
                        // $mod	    = array(Ounun_Default_Method);
                        $module		= scfg::def_mod;
                        $filename 	= scfg::$dir_root_app . "module/system.class.php";
                    }
                }
            } // end \Dir_App . "module/" . $mod[0] . '.class.php';
        }
        else
        {
            // 默认模块 与 默认方法
            $mod				= [scfg::def_met];
            $module				=  scfg::def_mod;
            $filename 			=  scfg::$dir_root_app . "module/system.class.php";
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
}

/** 注册自动加载 */
spl_autoload_register('\\ounun\\scfg::autoload');