<?php
/** 命名空间 */
namespace ounun;

/** ounun根目录 */
define('Ounun_Dir', 		 realpath(__DIR__) .'/');
/** 默认模块名称 */
define('Ounun_Def_Mod', 	'system');
/** 默认操作名称 */
define('Ounun_Def_Met', 	'index');

/**
 * 得到访客的IP
 * @return string IP
 */
function ip():string
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
function url(string $url,array $data,array $exts=[],array $skip=[]):string
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
function url_original(string $uri =''):string
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
function url_to_mod(string $uri,string $root = '/'):array
{
	$uri 	= \explode($root,    $uri, 					2);
	$uri 	= \explode('.', 	 urldecode($uri[1]),	2);
	$uri	= \explode('/', 	 $uri[0]                 );
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
function url_check(string $url_original="",bool $ext_req=true,string $domain=null)
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
        go_url($url_reset,false,301);
    }else if($url_reset)
    {
        // exit("\$url_reset:{$url_reset}\n");
        go_url($url_reset,false,301);
    }
    // exit("\$domain:{$domain}\n");
}

/**
 * @param string $url1
 * @param string $url2
 * @param string $note
 * @param bool $top
 */
function go_note(string $url1,string $url2,string $note,bool $top=false):void
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
function go_url(string $url,bool $top=false,int $head_code=302,int $delay=0):void
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
function go_back():void
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
function go_msg(string $msg,string $url = ''):void
{
    if($url)
    {
        exit(msg($msg).'<meta http-equiv="refresh" content="0.5;url=' . $url . '">');
    }else
    {
        echo msg($msg);
        go_back();
    }
}

/**
 * 获得 json字符串数据
 * @param $data
 * @return string
 */
function json_encode($data):string
{
    return \json_encode($data,JSON_UNESCAPED_UNICODE);
}

/**
 * 对 json格式的字符串进行解码
 * @param string $json_string
 * @return mixed
 */
function json_decode(string $json_string)
{
    return \json_decode($json_string,true);
}

/**
 * 获得 exts数据php
 * @param string $exts_string
 * @return array|mixed
 */
function exts_decode_php(string $exts_string)
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
function exts_decode_json(string $exts_string)
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
function base64_url_encode(string $string = null):string
{
    return strtr(base64_encode($string), '+/=', '-_~');
}

/**
 * 解码一个 URL传递的字符串
 *
 * @param string $string to decode
 * @return string
 */
function base64_url_decode(string $string = null):string
{
    return base64_decode(strtr($string, '-_~', '+/='));
}

/**
 * 编号 转 字符串
 *
 * @param string $string to encode
 * @return string
 */
function short_url_encode(int $id = 0):string
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
function short_url_decode(string $string = ''):int
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
function msg(string $msg, bool $outer = true, $meta = true):string
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
 */
function msg_close(string $msg,bool $close=false)
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
 * @param $data_mod
 * @param bool $is_app
 */
function data(string $data_mod,$is_app=false)
{
    $filename  = ($is_app?Dir_Libs:Dir_Libs_ProJ)."data.{$data_mod}.ini.php";
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
function expires(int $expires = 0,string $etag = '', int $LastModified = 0)
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
function error404($msg=''):void
{
    if(function_exists('\error404'))
    {
        \error404();
    }
    header('HTTP/1.1 404 Not Found');
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
function explodes(string $delimiters,string $string)
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
function safe(string $string):string
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
function sanitize(string $string, bool $spaces = true):string
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
function sanitize_url(string $string):string
{
	return urlencode(mb_strtolower(sanitize($string, FALSE)));
}

/**
 * Filter a valid UTF-8 string to be file name safe.
 *
 * @param string $string to filter
 * @return string
 */
function sanitize_filename(string $string):string
{
	return sanitize($string, FALSE);
}

/**
 * 返回基类
 * Class Ret
 * @package ounun
 */
class ret
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
        $this->debug = new \debug(\Dir_Root.'logs/error_404_'.date('Ymd').'.txt',false,false,false,true);
        error404("\$method:{$method} \$args:[".implode(',',$args[0])."]");
	}

	/**
	 * DB 相关
	 * @param sting $key enum:member,goods,admin,msg,help
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
            $db_cfg = $GLOBALS['scfg']['db'][$key];
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
	public function __construct($mod)
	{
        if(!$mod)
		{
			$mod = [cfg::Def_Met];
		}
        $method  = $mod[0];
		$this->$method( $mod );
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
	 * Template句柄容器
	 * @var \ounun\Tpl
	 */
	protected $_stpl = null;
    /**
     * 模板驱动
     * @var string null | php | smarty
     */
	protected $_stpl_drive = null;

    /**
     * 默认赋值(空)
     */
    protected function _global_assign(){}

    /**
     * 初始化HTMl模板类
     */
	public function template()
	{
		if(null == $this->_stpl)
        {
            require Ounun_Dir. 'ounun/tpl.class.php';
            $this->_stpl  = new tpl(Ounun_Dir_Tpl,$this->_stpl_drive);
        }
        $this->_global_assign();
	}

    /**
     * 默认 首页
     * @param array $mod
     */
    public function index($mod)
    {
        \ounun\error404();
    }

    /**
     * 默认 robots.txt文件
     * @param array $mod
     */
    public function robots($mod)
    {
        \ounun\url_check('/robots.txt');
        header('Content-Type: text/plain');
        if(file_exists(Dir_App.'robots.txt'))
        {
            readfile(Dir_App.'robots.txt');
        }else
        {
            exit("User-agent: *\nDisallow:");
        }
    }
    /**
	 * 赋值
	 * @param string|array $name
	 * @param mix $value
	 */
	public function assign($name, $val = null)
	{
		$this->_stpl->assign($name, $val);
	}

	/**
	 * 包含
	 * @param string $filename
	 */
	public function import($filename,$assign=[])
	{
		$this->_stpl->import($filename,$assign);
	}

    /**
     * 输出
     * @param string $filename
     */
    public function output($filename,$assign=[])
    {
        $this->_stpl->output($filename,$assign);
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
//function route(array $routes,string $host,array &$mod,string $default_app)
//{
//    if($routes && $mod && $mod[0] && $routes["{$host}/{$mod[0]}"])
//    {
//        $mod_0 = array_shift($mod);
//        return $routes["{$host}/{$mod_0}"];
//    }elseif($routes && $routes[$host])
//    {
//        return $routes[$host];
//    }
//    return $default_app;
//}

class scfg
{

    /** 路由数据 */
    protected $routes = [
        //         'www.866bet.com/api'  => ['app'=>'api', 'cls'=> 'site' ],         /* 数据接口 */
        //                 '138.vc/api'  => ['app'=>'api', 'cls'=> 'site' ],         /* 数据接口 */
        //        'www2.866bet.com/api'  => ['app'=>'api', 'cls'=> 'site' ],         /* 数据接口 */
    ];

    /** 路由数据(默认) */
    protected $routes_default = ['app'=>'www', 'cls'=> 'site'];

    /** html缓存配制  */
    protected $cache_html     = [
        'type'          => \ounun\cache::Type_File,
        'mod'  			=> 'html',
        'root' 			=> Dir_Cache,
        'format_string' => false,
        'large_scale' 	=> true,
    ];

    /** @var string 当前数据 */
    public static $app      = '';

    /** @var \cfg\site 站点常量包 */
    public static $site;

    /** @var \cfg\i18n\zh_cn 语言包 */
    public static $i18n;

    /** @return \cfg\site 站点常量包 */
    public static function site()
    {
        return \scfg::$site;
    }

    /** @return \cfg\i18n\zh_cn 语言包 */
    public static function i18n()
    {
        return \scfg::$i18n;
    }

    /**
     * 静态地址
     * @param string|array $url
     * @param string       $pre_str
     * @param bool         $is_web  true:Web静态 false:Image图片
     * @return string
     */
    static public function surl($url,string $pre_str="",bool $is_web=true):string
    {
        if($url && is_array($url) )
        {
            $url = count($url) > 1 ? '??'.implode(',',$url) : $url[0];
        }
        return ($is_web?Const_Url_Static_Web:Const_Url_Static_Image).$pre_str.$url;
    }

    /** @return string 认别用户语言 */
    public static function lang($domain_valid=false)
    {
        if($domain_valid)
        {
            return (   \cfg::Site_Domain_En_Pc == $_SERVER['HTTP_HOST']
                || \cfg::Site_Domain_En_M  == $_SERVER['HTTP_HOST'] )
                ? 'en'
                : 'zh_cn';
        }
        $lang = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
        $lang = substr($lang,0,2);
        $lang = strtolower($lang);
        return 'zh' == $lang ? 'zh_cn' :'en';
    }

    /**
     * 自动加载的类
     * @param $class_name
     */
    public static function autoload($class_name)
    {
        $class_name = ltrim($class_name, '\\');
        $lists 	    = explode('\\', $class_name);
        if('app' == $lists[0])
        {
            array_shift($lists);
            $file_name  = implode('/', $lists).'.class.php';
            $file_name  =\Dir_Libs  . $file_name;
            if(file_exists($file_name))
            {
                require $file_name;
            }
        }else
        {
            $file_name0   = implode('/', $lists) . '.class.php';
            $file_name    = \Dir_Libs_ProJ . $file_name0;
            if (file_exists($file_name))
            {
                require $file_name;
            } else
            {
                $file_name = \Dir_Lib . $file_name0;
                if (file_exists($file_name))
                {
                    require $file_name;
                }
            }
        }
    }

    /**
     * scfg constructor.
     * @param string $host
     * @param array $mod
     */
    public function __construct(string $host,array &$mod)
    {
        $this->start($host,$mod);
    }

    /**
     * 世界从这里开始(路由)
     * @param string $host
     * @param array $mod
     */
    public function start(string $host,array &$mod)
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
        self::$app  = $val_0['app'];
        self::$site = "\\cfg\\".$val_0['cls'];
        self::$i18n = "\\cfg\\i18n\\".(self::$site::lang);

        /** 重定义头 */
        header('X-Powered-By: Ounun.org');
        /** 应用目录 */
        define('Dir_App',           	\Dir_Root. 'app.'.self::$app.'/');
        /** Libs目录 **/
        define('Dir_Libs',        	    \Dir_App . 'libs/');
        /** 模块所在目录 */
        define('Ounun_Dir_Module', 	    \Dir_App . 'module/');
        /** 模板存放目录 */
        define('Ounun_Dir_Tpl', 	    \Dir_App . 'template/');
        /** 加载libs/scfg.{self::$app}.ini.php文件 */
        $filename   = Dir_Libs . "scfg.".self::$app.".ini.php";
        if(file_exists($filename))
        {
            require $filename;
        }

        // 设定 模块与方法
        if(is_array($mod) && $mod[0])
        {
            $filename         = Ounun_Dir_Module . $mod[0] . '.class.php';
            if(file_exists($filename))
            {
                $module		  = $mod[0];
                if($mod[1])
                {
                    array_shift($mod);
                }else
                {
                    $mod	  = [Ounun_Def_Met];
                }
            }
            else
            {
                if($mod[1])
                {
                    $filename = Ounun_Dir_Module . "{$mod[0]}/{$mod[1]}.class.php";
                    if(file_exists($filename))
                    {
                        $module		    = $mod[0].'\\'.$mod[1];
                        if($mod[2])
                        {
                            array_shift($mod);
                            array_shift($mod);
                        }else
                        {
                            $mod	   = [Ounun_Def_Met];
                        }
                    }else
                    {
                        $filename       = Ounun_Dir_Module . $mod[0].'/'.Ounun_Def_Mod.'.class.php';
                        if(file_exists(Ounun_Dir_Module . $mod[0]) && file_exists($filename))
                        {
                            $module	    = $mod[0].'\\'.Ounun_Def_Mod;
                            array_shift($mod);
                        }else
                        {
                            // 默认模块
                            $module		= Ounun_Def_Mod;
                            $filename 	= Ounun_Dir_Module . $module . '.class.php';
                        }
                    }
                }else
                {
                    $filename = Ounun_Dir_Module . $mod[0].'/'.Ounun_Def_Mod.'.class.php';
                    // exit($filename);
                    if(file_exists($filename))
                    {
                        $module		= $mod[0].'\\'.Ounun_Def_Mod;
                        $mod	    = [Ounun_Def_Met];
                        // array_shift($mod);
                    }else
                    {
                        // 默认模块
                        // $mod	    = array(Ounun_Default_Method);
                        $module		= Ounun_Def_Mod;
                        $filename 	= Ounun_Dir_Module . $module . '.class.php';
                    }
                }
            } // end Ounun_Dir_Module . $mod[0] . '.class.php';
        }
        else
        {
            // 默认模块 与 默认方法
            $mod				= [Ounun_Def_Met];
            $module				=  Ounun_Def_Mod;
            $filename 			=  Ounun_Dir_Module . $module . '.class.php';
        }
        // 包括模块文件
        require $filename;
        // 初始化类
        $module  				= '\\module\\'.$module;
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
