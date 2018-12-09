<?php
namespace ounun;
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
        $this->debug = new \ounun\debug(scfg::$dir_root.'logs/error_404_'.date('Ymd').'.txt',false,false,false,true);
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
	 *  @var  template
     **/
	protected static $_stpl = null;

    /** 初始化HTMl模板类 */
	public function template(string $style_name = '',string $style_name_default='',string $dir_tpl_root='',string $dir_tpl_root_g = '')
	{
		if(null == self::$_stpl)
        {
            $dir_tpl_root   = $dir_tpl_root  ?$dir_tpl_root  :scfg::$dir_root_app    . 'template/';
            $dir_tpl_root_g = $dir_tpl_root_g?$dir_tpl_root_g:dirname(scfg::$lib_cms). '/cms.single.template.v1/';
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
     * @var \ounun\debug
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
    const def_mod   = 'index';
    /** @var string 默认操作名称 */
    const def_met   = 'index';

    /** @var array 公共配制数据  */
    static public $g             = [];
    /** @var array DB配制数据  */
    static public $db            = [];
    /** @var array 自动加载路径paths  */
    static public $maps_paths    = [];
    /** @var array 自动加载路径maps  */
    static public $maps_class    = [];
    /** 路由模块数据  */
    static public $mod           = [];


    /** @var string 根目录 */
    static public $dir_root      = '';
    /** @var string Ounun目录   */
    static public $dir_ounun     =  __DIR__.'/';
    /** @var string 根目录(App) */
    static public $dir_app       = '';

    /** @var string Www URL */
    static public $url_www       = '';
    /** @var string Mobile URL */
    static public $url_wap       = '';
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

    /** @var \model\i18n 语言包 */
    static public $i18n;
    /** @var string 当前语言 */
    static public $lang         = 'zh_CN';
    /** @var string 默认语言 */
    static public $lang_default = 'zh_CN';
    /** @var array 支持的语言 */
    public static $langs  = [
        "en_us"=>"English",
        // "zh"=>"繁體中文",
        "zh_cn"=>"简体中文",
        // "ja"=>"日本語",
    ];

    /**
     * 设定语言
     * @param string $lang
     * @param string $lang_default
     */
    static public function set_lang(string $lang,string $lang_default = '')
    {
        $lang && self::$lang = $lang;
        $lang_default && self::$lang_default = $lang_default;
        if($lang  == self::$lang_default)
        {
            self::$i18n     = "\\model\\i18n";
        }else
        {
            self::$i18n     = "\\model\\i18n\\{$lang}";
        }
    }

    /**
     * 设定支持的语言
     * @param array $langs
     */
    static public function set_lang_support(array $langs = [])
    {
        if($langs)
        {
            foreach ($langs as $lang=>$lang_name)
            {
                self::$langs[$lang] = $lang_name;
            }
        }
    }

    /**
     * 设定公共配制数据
     * @param array $cfg
     */
    static public function set_global(array $cfgs = [])
    {
        if($cfgs)
        {
            foreach ($cfgs as $cfg=>$data)
            {
                self::$g[$cfg] = $data;
            }
        }
    }


    /**
     * 设定DB配制数据
     * @param array $cfg
     */
    static public function set_database(array $database_cfg = [])
    {
        if($database_cfg)
        {
            foreach ($database_cfg as $db_key=>$db_cfg)
            {
                self::$db[$db_key] = $db_cfg;
            }
        }
    }

    /** 设定路由数据 */
    static public function set_routes(array $routes,array $routes_default = [])
    {
        if($routes)
        {
            foreach ($routes as $k=>$v)
            {
                self::$routes[$k] = $v;
            }
        }
        if($routes_default)
        {
            self::$routes_default = $routes_default;
        }
    }


    /**
     * 设定地址
     * @param string $url_www
     * @param string $url_mobile
     * @param string $url_mip
     * @param string $url_api
     * @param string $url_res
     * @param string $url_static
     * @param string $url_static_g
     * @param string $app_domain
     */
    static public function set_urls(string $url_www,string $url_wap,string $url_mip,string $url_api,string $url_res,string $url_static,string $url_static_g,string $app_domain)
    {
        /** Www URL */
        self::$url_www       = $url_www;
        /** Mobile URL */
        self::$url_wap       = $url_wap;
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
     * 设定目录
     * @param string $dir_ounun
     * @param string $dir_root
     * @param string $dir_app
     */
    static public function set_dirs(string $dir_ounun,string $dir_root,string $dir_app = '')
    {
        // Ounun目录
        $dir_ounun  && self::$dir_ounun = $dir_ounun;
        // 根目录
        $dir_root   && self::$dir_root  = $dir_root;
        // APP目录
        if($dir_app)
        {
            self::$dir_app = $dir_app;
        }elseif(!self::$dir_app)
        {
            self::$dir_app = Dir_App.self::$app.'/';
        }
    }

    /** @return \model\i18n 语言包 */
    static public function get_i18n()
    {
        return self::$i18n;
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
     * 添加自动加载路径
     * @param array $paths
     * @param string $namespace_prefix
     */
    static public function add_paths(array $paths,string $namespace_prefix = '')
    {
        if($paths && is_array($paths))
        {
            if($namespace_prefix)
            {
                $first  = $namespace_prefix[0];
                foreach ($paths as $path)
                {
                    if($path && ( !self::$maps_paths  ||  !self::$maps_paths[$first]  || !self::$maps_paths[$first][$namespace_prefix]  ||  !in_array($path,self::$maps_paths[$first][$namespace_prefix])  ))
                    {
                        self::$maps_paths[$first][$namespace_prefix][] = $path;
                    }
                }
            }else
            {
                foreach ($paths as $path)
                {
                    if($path && ( !self::$maps_paths  ||  !self::$maps_paths['']  ||  !in_array($path,self::$maps_paths['']) ))
                    {
                        self::$maps_paths[''][] = $path;
                    }
                }
            }
        }
    }

    /**
     * 添加类库映射
     * @param $class
     * @param $filename
     */
    static public function add_class($class,$filename)
    {
        self::$maps_class[$class] = $filename;
    }


    /**
     * 自动加载的类
     * @param $class_name
     */
    static public function autoload($class)
    {
        echo "\$class:{$class}\n";

        // 类库映射
        if (!empty(self::$maps_class[$class]))
        {
            return self::$maps_class[$class];
        }

        print_r(['self::$maps_class'=>self::$maps_class,'self::$maps_paths'=>self::$maps_paths]);

        // 查找 PSR-4 prefix
        $filename  = strtr($class, '\\', '/') . '.php';
        $first     = $class[0];
        if (isset(self::$maps_paths[$first]))
        {
            foreach (self::$maps_paths[$first] as $prefix => $paths)
            {
                if (0 === strpos($class, $prefix))
                {
                    $length = strlen($prefix);
                    foreach ($paths as $dir)
                    {
                        $file = $dir  . substr($filename, $length);
                        echo "\$file:{$file}\n";
                        if(is_file($file))
                        {
                            require $file;
                        }
                    }
                }
            }

        }

        // 查找 PSR-4 prefix = ''
        if (isset(self::$maps_paths['']))
        {
            foreach (self::$maps_paths[''] as $dir)
            {
                $file = $dir  . $filename;
                echo "\$file:{$file}\n";
                if(is_file($file))
                {
                    require $file;
                }
            }
        }
    }

    /**
     * 配制文件
     * @param string $host
     * @param array $mod
     */
    static public function init(array $mod,string $host)
    {
        /** 语言 */
        if($mod && $mod[0] && self::$langs[$mod[0]])
        {
            $lang = array_shift($mod);
        }else
        {
            $lang = self::$lang ? self::$lang : self::$lang_default;
        }
        self::set_lang($lang);

        if($mod && $mod[0] && self::$routes["{$host}/{$mod[0]}"])
        {
            $mod_0 = array_shift($mod);
            $val_0 = self::$routes["{$host}/{$mod_0}"];
        }elseif(self::$routes[$host])
        {
            $val_0 = self::$routes[$host];
        }else
        {
            $val_0 = self::$routes_default;
        }
        // $app
        self::$app           = $val_0['app'];
        self::$app_url       = $val_0['url'];

        // set_dirs
        self::set_dirs(Dir_Ounun,Dir_Root);
        self::add_paths([self::$dir_ounun,self::$dir_app]);

        /** @var string 模板 */
        self::$tpl           = $val_0['tpl']?$val_0['tpl']:self::get_i18n()::tpl;
        self::$tpl_default   = $val_0['tpl_default']?$val_0['tpl_default']:self::get_i18n()::tpl_default;
    }

    /** 路由数据 */
    static protected $routes = [
        //         'www.866bet.com/api'  => ['app'=>'api', 'cls'=> 'site' ],         /* 数据接口 */
        //                 '138.vc/api'  => ['app'=>'api', 'cls'=> 'site' ],         /* 数据接口 */
        //        'www2.866bet.com/api'  => ['app'=>'api', 'cls'=> 'site' ],         /* 数据接口 */
    ];

    /** 路由数据(默认) */
    static protected $routes_default = ['app'=>'www', 'url' => '/'];
}

/** 开始 */
function start($argv)
{
    // 解析URL
    if($argv && $argv[1])
    {
        // error_reporting(E_ALL ^ E_NOTICE);
        $mod    = $argv[1];
        $mod    = explode(',', $mod);
        $host   = $argv[2]?$argv[2]:'adm';
        if('zrun_' != substr($mod[0],0,5) )
        {
            exit("error php shell only:zrun_*\n");
        }
    }else
    {
        $uri 	= url_original($_SERVER['REQUEST_URI']);
        $mod	= url_to_mod($uri);
    }
    scfg::init($mod,$_SERVER["HTTP_HOST"]);

    /** 加载common */
    file_exists(Dir_App.'common.php') && require Dir_App.'common.php';
    /** 加载config */
    file_exists(Dir_App.'config.php') && require Dir_App.'config.php';
    /** 加载config-xxx */
    if(Environment && file_exists(Dir_App.'config'.Environment.'.php'))
    {
        echo "f:".Dir_App.'config'.Environment.'.php'."\n";
        require Dir_App.'config'.Environment.'.php';
    }

    /** 加载common */
    file_exists(scfg::$dir_app.'common.php') && require scfg::$dir_app.'common.php';
    /** 加载config */
    file_exists(scfg::$dir_app.'config.php') && require scfg::$dir_app.'config.php';
    /** 加载config-xxx */
    if(Environment && file_exists(scfg::$dir_app.'config'.Environment.'.php'))
    {
        echo "f2:".scfg::$dir_app.'config'.Environment.'.php'."\n";
        require scfg::$dir_app.'config'.Environment.'.php';
    }

    /** 开始 */
    // 重定义头 ---------------------------------
    header('X-Powered-By: Ounun.org');

    // 设定 模块与方法
    if(is_array($mod) && $mod[0])
    {
        $filename         = scfg::$dir_app . "module/{$mod[0]}.php";
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
                $filename           = scfg::$dir_app . "controller/{$mod[0]}/{$mod[1]}.class.php";
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
                    $filename       = scfg::$dir_app . "controller/{$mod[0]}/index.class.php";
                    if(file_exists(scfg::$dir_app . "controller/{$mod[0]}" ) && file_exists($filename))
                    {
                        $module	    = "{$mod[0]}\\index";
                        array_shift($mod);
                    }else
                    {
                        $module		= scfg::def_mod;
                        $filename 	= scfg::$dir_app . "controller/index.class.php";
                    }
                }
            }else
            {
                $filename       = scfg::$dir_app . "controller/{$mod[0]}/index.class.php";
                if(file_exists($filename))
                {
                    $module		= "{$mod[0]}\\controller";
                    $mod	    =  [scfg::def_met];
                    // array_shift($mod);
                }else
                {
                    // 默认模块
                    // $mod	    = array(Ounun_Default_Method);
                    $module		= scfg::def_mod;
                    $filename 	= scfg::$dir_app . "controller/index.class.php";
                }
            }
        } // end \Dir_App . "module/" . $mod[0] . '.class.php';
    }
    else
    {
        // 默认模块 与 默认方法
        $mod				= [scfg::def_met];
        $module				=  scfg::def_mod;
        $filename 			=  scfg::$dir_app . "controller/index.class.php";
    }
    // 包括模块文件
    require $filename;
    // 初始化类
    $module  				= "\\controller\\{$module}";
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

/** 加载common.php */
require __DIR__.'/common.php';
/** 注册自动加载 */
spl_autoload_register('\\ounun\\scfg::autoload');
