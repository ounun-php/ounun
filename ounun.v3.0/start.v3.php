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
        $this->debug = new \ounun\debug(scfg::$dir_root.'public/logs/error_404_'.date('Ymd').'.txt',false,false,false,true);
        error404("\$method:{$method} \$args:".json_encode($args)."");
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
        if(empty(self::$_db[$key])) {
            if(null == $db_cfg) {
                $db_cfg = scfg::$db_cfg[$key];
            }
            self::$_db[$key] = new \ounun\mysqli($db_cfg);
        }
        // print_r([$key=>$db_cfg,'db'=>self::$_db[$key]]);
		// self::$_db[$key]->active();
		return self::$_db[$key];
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
    const def_module   = 'index';
    /** @var string 默认操作名称 */
    const def_method   = 'index';

    /** @var array 公共配制数据  */
    static public $g   = [];
    /** @var \v */
    static public $view;
    /** @var array DB配制数据  */
    static public $db_cfg        = [];
    /** @var array 自动加载路径paths  */
    static public $maps_paths    = [];
    /** @var array 自动加载路径maps  */
    static public $maps_class    = [];

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
    /** @var string Upload URL */
    static public $url_upload    = '';
    /** @var string StaticG URL */
    static public $url_static_g  = '';

    /** @var string 当前APP */
    static public $app_name      = '';
    /** @var string 当前APP Path */
    static public $app_path      = '';
    /** @var string 域名Domain */
    static public $app_domain    = '';
    /** @var string 对应cms类名  */
    static public $app_cms_classname;

    /** @var string 模板-样式 */
    static public $tpl_style     = '';
    /** @var string 模板-样式[默认] */
    static public $tpl_default   = '';
    /** @var array Template view目录 */
    static public $tpl_dirs      = [];
    /** @var array 模板替换数据组 */
    static public $tpl_data      = [];

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
     * 设定对应cms类名
     * @param string $cms_classname
     */
    static public function set_cms_classname(string $cms_classname = '\\extend\\cms_www')
    {
        self::$app_cms_classname = $cms_classname;
    }

    /**
     * 设定语言
     * @param string $lang
     * @param string $lang_default
     */
    static public function set_lang(string $lang,string $lang_default = '')
    {
        $lang && self::$lang = $lang;
        $lang_default && self::$lang_default = $lang_default;
        if($lang  == self::$lang_default) {
            self::$i18n     = '\\app\\'.scfg::$app_name.'\\model\\i18n';
        } else {
            self::$i18n     = '\\app\\'.scfg::$app_name.'\\model\\i18n\\'.$lang;
        }
    }

    /**
     * 设定支持的语言
     * @param array $langs
     */
    static public function set_lang_support(array $langs = [])
    {
        if($langs) {
            foreach ($langs as $lang=>$lang_name) {
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
        if($cfgs) {
            foreach ($cfgs as $cfg=>$data) {
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
        if($database_cfg) {
            foreach ($database_cfg as $db_key=>$db_cfg) {
                self::$db_cfg[$db_key] = $db_cfg;
            }
        }
    }

    /** 设定路由数据 */
    static public function set_routes(array $routes,array $routes_default = [])
    {
        if($routes) {
            foreach ($routes as $k=>$v) {
                self::$routes[$k] = $v;
            }
        }
        if($routes_default) {
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
    static public function set_urls(string $url_www,string $url_wap,string $url_mip,string $url_api,string $url_res,string $url_static,string $url_upload,string $url_static_g,string $app_domain)
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
        /** Upload URL */
        self::$url_upload    = $url_upload;
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
    static public function set_dirs(string $dir_ounun, string $dir_root, string $app_name, string $app_path, string $dir_app = '')
    {
        // 当前APP
        $app_name   && self::$app_name  = $app_name;
        // 当前APP Path
        $app_path   && self::$app_path   = $app_path;
        // Ounun目录
        $dir_ounun  && self::$dir_ounun = $dir_ounun;
        // 根目录
        $dir_root   && self::$dir_root  = $dir_root;
        // APP目录
        if($dir_app) {
            self::$dir_app = $dir_app;
        }elseif(!self::$dir_app) {
            self::$dir_app = Dir_App.self::$app_name.'/';
        }
    }

    /**
     * 设定 模板根目录
     * @param string $tpl_dir
     */
    static public function set_tpl_dirs(string $tpl_dir)
    {
        if( !in_array($tpl_dir,self::$tpl_dirs) ) {
            self::$tpl_dirs[] = $tpl_dir;
        }
    }

    /**
     * 设定模板替换
     * @param string $key
     * @param string $value
     */
    static public function set_tpl_data(string $key, string $value)
    {
        self::$tpl_data[$key] = $value;
    }

    /**
     * 设定模板替换
     * @param array $data
     */
    static public function set_tpl_array(array $data)
    {
        if($data && is_array($data)) {
            foreach ($data as $key => $value) {
                self::$tpl_data[$key] = $value;
            }
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
        if(!$lang) {
            $lang = self::$lang;
        }
        if($url !== '' && $url[0] == '/') {
            if($lang == self::$lang_default) {
                return self::$app_path.substr($url,1);
            }
            return '/'.$lang.self::$app_path.substr($url,1);
        } else {
            if($lang == self::$lang_default) {
                return self::$app_path.$url;
            }
            return '/'.$lang.self::$app_path.$url;
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
        if($url && is_array($url) ) {
            $url = count($url) > 1 ? '??'.implode(',',$url) : $url[0];
        }
        return "{$static_root}{$url}";
    }

    /**
     * 添加自动加载路径
     * @param string $path              目录路径
     * @param string $namespace_prefix  命名空间
     * @param bool $cut_path            是否剪切 目录路径中的 命名空间
     */
    static public function add_paths(string $path,string $namespace_prefix = '',bool $cut_path = false)
    {
        if($path) {

            if($namespace_prefix) {
                $first  = explode('\\', $namespace_prefix)[0];
                $len    = strlen($namespace_prefix)+1;
            }else {
                $first  = '';
                $len    = 0;
            }
            if(!self::$maps_paths  ||  !self::$maps_paths[$first]  ||  !in_array($path,self::$maps_paths[$first]) ) {
                self::$maps_paths[$first][] = [
                    'path'      => $path,
                    'len'       => $len ,
                    'cut'       => $cut_path,
                    'namespace' => $namespace_prefix
                ];
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
    static public function load_class($class)
    {
        // 类库映射
        if (!empty(self::$maps_class[$class])) {
            require self::$maps_class[$class];
            return;
        }

        // print_r(['$class'=>$class,'self::$maps_class'=>self::$maps_class,'self::$maps_paths'=>self::$maps_paths]);
        // 查找 PSR-4 prefix
        $filename  = strtr($class, '\\', '/') . '.php';
        $firsts    = [explode('\\', $class)[0],''];
        foreach ($firsts as $first) {
            if (isset(self::$maps_paths[$first])) {
                foreach (self::$maps_paths[$first] as $v) {
                    if (0 === strpos($class, $v['namespace'])) {
                        $file = $v['path'].(($v['cut'] && $v['len'])?substr($filename, $v['len']):$filename);
//                        echo " load_class  -> \$class :{$class}  \$len:{$v['len']}\n".
//                             "                \t\t\$path:{$v['path']}\n".
//                             "                \t\t\$filename:{$filename}\n".
//                             "                \t\t\$file1:{$file} \n";
                        if(is_file($file)) {
                            require $file;
                            return;
                        }
                    }
                }
            }
        }
    }

    /**
     * 加载controller
     * @param $controller_file
     * @return string
     */
    static public function load_controller($controller_file)
    {
        $controller = self::$maps_paths['app'];
        if($controller && is_array($controller)) {
            foreach ($controller as $v) {
                $filename  = $v['path'] . $controller_file;
                // echo "\$filename:{$filename}\n";
                if(file_exists($filename)) {
                    return $filename;
                }
            }
            //$dirs = "\ndirs:[".implode("],[",array_column($controller,'path'))."]";
        }
        return '';
    }

    /**
     * 加载Config
     * @param $dir
     */
    static public function load_config($dir)
    {
        /** 加载common */
        file_exists($dir.'common.php') && require $dir.'common.php';
        // echo 'load_config -> '.__LINE__.':'.(file_exists($dir.'common.php')?'1':'0').' '.$dir.'common.php'."\n";
        /** 加载config */
        file_exists($dir.'config.php') && require $dir.'config.php';
        // echo 'load_config -> '.__LINE__.':'.(file_exists($dir.'config.php')?'1':'0').' '.$dir.'config.php'."\n";
        /** 加载config-xxx */
        if(Environment && file_exists($dir.'config'.Environment.'.php')) {
            require $dir.'config'.Environment.'.php';
            // echo 'load_config -> '.__LINE__.':'.(file_exists($dir.'config'.Environment.'.php')?'1':'0').' '.$dir.'config'.Environment.'.php'."\n";
        }
    }



    /** 路由数据 */
    static public $routes = [
        //         'www.866bet.com/api'  => ['app'=>'api', 'cls'=> 'site' ],         /* 数据接口 */
        //                 '138.vc/api'  => ['app'=>'api', 'cls'=> 'site' ],         /* 数据接口 */
        //        'www2.866bet.com/api'  => ['app'=>'api', 'cls'=> 'site' ],         /* 数据接口 */
    ];

    /** 路由数据(默认) */
    static public $routes_default = ['app'=>'www', 'url' => '/'];
}


/** 开始 */
function start(array $mod,string $host)
{
    // 语言
    if($mod && $mod[0] && scfg::$langs[$mod[0]]) {
        $lang = array_shift($mod);
    } else {
        $lang = scfg::$lang ? scfg::$lang : scfg::$lang_default;
    }
    // load_config 0 Dir
    \ounun\scfg::load_config(Dir_App);

    // Routes
    if($mod && $mod[0] && scfg::$routes["{$host}/{$mod[0]}"]) {
        $mod_0 = array_shift($mod);
        $val_0 = scfg::$routes["{$host}/{$mod_0}"];
    }elseif(scfg::$routes[$host]) {
        $val_0 = scfg::$routes[$host];
    }else {
        $val_0 = scfg::$routes_default;
    }

    // set_dirs
    scfg::set_dirs(Dir_Ounun,Dir_Root,$val_0['app'],$val_0['url']);

    // set_lang
    scfg::set_lang($lang);

    // set_tpl_dirs
    scfg::set_tpl_dirs( scfg::$dir_app.'view/');

    // add_paths
    scfg::add_paths(scfg::$dir_app,'app\\'.scfg::$app_name,true);

    // load_config 1 scfg::$dir_app
    scfg::load_config(scfg::$dir_app);

    // 模板
    scfg::$tpl_style     = $val_0['tpl_style']  ?$val_0['tpl_style']  :scfg::get_i18n()::tpl_style;
    scfg::$tpl_default   = $val_0['tpl_default']?$val_0['tpl_default']:scfg::get_i18n()::tpl_default;

    // 开始 重定义头
    header('X-Powered-By: Ounun.org');

    // 设定 模块与方法
    if(is_array($mod) && $mod[0]) {
        $filename         = scfg::load_controller("controller/{$mod[0]}.php");
        if(file_exists($filename)) {
            $module		  = $mod[0];
            if($mod[1]) {
                array_shift($mod);
            } else {
                $mod	  = [scfg::def_method];
            }
        } else {
            if($mod[1]) {
                $filename           = scfg::load_controller("controller/{$mod[0]}/{$mod[1]}.php");
                if(file_exists($filename)) {
                    $module		    = $mod[0].'\\'.$mod[1];
                    if($mod[2]) {
                        array_shift($mod);
                        array_shift($mod);
                    }else {
                        $mod	    = [scfg::def_method];
                    }
                } else {
                    $filename       = scfg::load_controller("controller/{$mod[0]}/index.php");
                    if(file_exists($filename)) {
                        $module	    = "{$mod[0]}\\index";
                        array_shift($mod);
                    } else {
                        $module		= scfg::def_module;
                        $filename 	= scfg::load_controller("controller/index.php");
                    }
                }
            } else {
                $filename       = scfg::load_controller("controller/{$mod[0]}/index.php");
                if(file_exists($filename)) {
                    $module		= "{$mod[0]}\\index";
                    $mod	    =  [scfg::def_method];
                    // array_shift($mod);
                } else {
                    // 默认模块
                    $module		= scfg::def_module;
                    $filename 	= scfg::load_controller("controller/index.php");
                }
            }
        } // end \Dir_App . "module/" . $mod[0] . '.php';
    } else {
        // 默认模块 与 默认方法
        $mod				= [scfg::def_method];
        $module				=  scfg::def_module;
        $filename 			=  scfg::load_controller("controller/index.php");
    }
    // 包括模块文件
    if($filename){
        require $filename;
        $module  				= '\\app\\'.scfg::$app_name.'\\controller\\'.$module ;
        if(class_exists($module,false)){
            new $module($mod);
            exit();
        } else {
            $error = "Can't find Module:'{$module}'.";
        }
    } else {
        $error = "Can't find controller:{$module} filename:".$filename;
    }
    header('HTTP/1.1 404 Not Found');
    trigger_error($error, E_USER_ERROR);
}

/** Web */
function start_web()
{
    $uri 	= url_original($_SERVER['REQUEST_URI']);
    $mod	= url_to_mod($uri);
    start($mod,$_SERVER['HTTP_HOST']);
}

/** Cmd */
function start_cmd($argv)
{
    $mod    = $argv[1];
    $mod    = explode(',', $mod);
    $host   = $argv[2]?$argv[2]:'adm';
    if('zrun_' != substr($mod[0],0,5) ) {
        exit("error php shell only:zrun_*\n");
    }
    start($mod,$host);
}

/** 加载common.php */
require __DIR__.'/common.php';
/** 注册自动加载 */
spl_autoload_register('\\ounun\\scfg::load_class');
