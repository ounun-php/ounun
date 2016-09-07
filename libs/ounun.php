<?php
/** 命名空间 */
namespace ounun;

/** ounun根目录 */
define('Ounun_Dir', 		 	    \Dir_Lib . 'ounun/');


if(defined('Const_Module'))
{
    /** 默认模块名称 */
    define('Ounun_Default_Module', 	 \Const_Module);

    /** 默认操作名称 */
    define('Ounun_Default_Method', 	 \Const_Method);
}else
{
    /** 默认模块名称 */
    define('Ounun_Default_Module', 	 'system');

    /** 默认操作名称 */
    define('Ounun_Default_Method', 	 'index');
}

/**
 * 得到访客的IP
 * @return string IP
 */
function ip()
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
function url($url,$data)
{
	$rs = array();
	if(is_array($data))
	{
		foreach ($data as $key => $value)
			$rs[] = $key.'='.urlencode($value);
	}
	return $url.(strstr($url,'?')?'&':'?').implode('&',$rs);
}

/**
 * 得到 原生 URL(去问号后的 QUERY_STRING)
 * @param $uri
 * @return string URL
 */
function url_original($uri)
{
	$tmp = explode('?', $uri, 2);
	return $tmp[0];
}

/**
 * 通过uri得到mod
 * @param $uri
 * @param string $root
 * @return array
 */
function url_to_mod($uri,$root = '/')
{
	$uri 	= \explode($root,    $uri, 					2);
	$uri 	= \explode('.', 	 urldecode($uri[1]),	2);
	$uri	= \explode('/', 	 $uri[0]                 );
	$mod	= array();
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
function url_check($url_original="",$ext_req=true,$domain=null)
{
    debug_header('url_check',$_SERVER['REQUEST_URI']);
    // URL去重
    $url            = explode('?',$_SERVER['REQUEST_URI'],2);
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
    // 域名
    if($domain && $domain != $_SERVER['HTTP_HOST'])
    {
        $domain     = $_SERVER['HTTP_HOST'];
        $url_reset  = $url_reset?$url_reset:$_SERVER['REQUEST_URI'];
        $url_reset  = "http://{$domain}{$url_reset}";
        go_url($url_reset,false,301);
    }else if($url_reset)
    {
        go_url($url_reset,false,301);
    }
}
/**
 * @param string $url1
 * @param string $url2
 * @param string $note
 * @param bool $top
 */
function go_note($url1,$url2,$note,$top=false)
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
function go_url($url,$top=false,$head_code=302,$delay=0)
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
function go_back()
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
function go_msg($msg,$url)
{
    exit(msg($msg).'<meta http-equiv="refresh" content="0;url=' . $url . '">');
}
/**
 * 彈出對話框
 *
 * @param string $msg
 * @param boolean $outer
 * @return string
 */
function msg($msg, $outer = true, $meta = true)
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
 * HTTP缓存控制
 *
 * @param int 		$expires		缓存时间 0:为不缓存 单位:s
 * @param string 	$etag			ETag
 * @param int 		$LastModified	最后更新时间
 */
function expires($expires = 0, $etag = '', $LastModified = 0)
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
function error404()
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
                    <h1>404 Not Found</h1>
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
 * 在header输出头数据
 * @param $k
 * @param $v
 * @param bool|false $debug
 */
function debug_header($k,$v,$debug=false,$funs='',$line='')
{
    static $idx = 0;
    if($debug)
    {
        $idx++;
        if($line)
        {
            $key[]     = $line;
            if($funs)
            {
                $key[] = $funs;
            }
            if($k)
            {
                $key[] = $k;
            }
        }else
        {
            $key[]     = $k;
            if($funs)
            {
                $key[] = $funs;
            }
        }
        $key       = implode('-',$key);
        $idx       = str_pad($idx,4,'0',STR_PAD_LEFT);
        header("{$idx}-{$key}: {$v}",false);
    }
}
/**
 * 出错提示错
 */
function error($error,$close=false)
{
    echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />',
    '<script type="text/javascript">',
        'alert(' . \json_encode($error) . ');',
    '</script>';

    if($close)
    {
        // 本页自动关闭.
        echo '<script type="text/javascript">window.opener = null; window.open("", "_self", ""); window.close(); </script>';
    }
    exit();
}
/**
 *
 * @param $delimiters
 * @param $string
 * @return array
 */
function explodes($delimiters,$string)
{
    $ready  = \str_replace($delimiters, $delimiters[0], $string);
    $launch = \explode($delimiters[0], $ready);
    return $launch;
}

/**
 * Convert special characters to HTML safe entities.
 * 特殊字符转换成 HTML安全格式。
 *
 * @param string $string to encode
 * @return string
 */
function safe($string)
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
function sanitize($string, $spaces = TRUE)
{
    $search = array(
        '/[^\w\-\. ]+/u',			// Remove non safe characters
        '/\s\s+/',					// Remove extra whitespace
        '/\.\.+/', '/--+/', '/__+/'	// Remove duplicate symbols
    );

    $string = preg_replace($search, array(' ', ' ', '.', '-', '_'), $string);

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
function sanitize_url($string)
{
	return urlencode(mb_strtolower(sanitize($string, FALSE)));
}
/**
 * Filter a valid UTF-8 string to be file name safe.
 *
 * @param string $string to filter
 * @return string
 */
function sanitize_filename($string)
{
	return sanitize($string, FALSE);
}

/**
 * 获得libs Data数据
 * @param $data_mod
 * @param bool $is_app
 */
function data($data_mod,$is_app=false)
{
    $filename  = ($is_app?Dir_Libs:Dir_Libs_ProJ)."data.{$data_mod}.ini.php";
    if(file_exists($filename))
    {
        return require $filename;
    }
    return null;
}
/**
 * Encode a string so it is safe to pass through the URL
 *
 * @param string $string to encode
 * @return string
 */
function base64_url_encode($string = NULL)
{
	return strtr(base64_encode($string), '+/=', '-_~');
}


/**
 * Decode a string passed through the URL
 *
 * @param string $string to decode
 * @return string
 */
function base64_url_decode($string = NULL)
{
    return base64_decode(strtr($string, '-_~', '+/='));
}


/**
 * 编号 转 字符串
 *
 * @param string $string to encode
 * @return string
 */
function short_url_encode($id = 0)
{
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
function short_url_decode($string = '')
{
    $p  = 0;
    while($string)
    {

        $s      = substr($string,0,1);
        $n      = is_numeric($s)?$s:ord($s);
        $p      = $p*62 + (($n >= 97)?( $n - 61) :( $n >= 65 ? $n - 55 : $n )) ;
        $string = substr($string,1);
    }
    return $p;
}

/**
 * 基类的基类
 * Class Base
 * @package ounun
 */
class Base
{
    /**
     * @var 默认方法
     */
	public $default_method = Ounun_Default_Method;

	/**
	 * 没定的方法
	 * @param String $method
	 * @param String $arg
	 */
	public function __call($method, $args)
	{
		header('HTTP/1.1 404 Not Found');
        error404();
	}

	/**
	 * DB 相关
	 * @param sting $key enum:member,goods,admin,msg,help
	 */
	private static $_db = array();

	/**
	 * 返回数据库连接对像
	 * @param  string $key
	 * @return \ounun\Mysqli
	 */
	public static function db($key)
	{
		self::$_db[$key] || self::$_db[$key] = new \ounun\Mysqli($GLOBALS['scfg']['db'][$key]);
		self::$_db[$key]->active();
		return self::$_db[$key];
	}

	/**
     * 调试 相关
     * @var \ounun\Debug
     */
    public $debug	= null;

    /**
     * 调试日志
     * @param $k
     * @param $log
     */
	public function debug_logs($k,$log)
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
}

/**
 * 构造模块基类
 * Class ViewBase
 * @package ounun
 */
class ViewBase extends Base
{
	public function __construct($mod)
	{
        if(!$mod)
		{
			$mod = array($this->default_method);
		}
        $method = $mod[0];
		$this->$method( $mod );
	}
	/**
	 * Template句柄容器
	 * @var \ounun\Tpl
	 */
	protected $_stpl = null;

    /**
     * 初始化HTMl模板类
     */
	public function Template()
	{
		if(null == $this->_stpl)
        {
            require Ounun_Dir. 'Tpl.class.php';
            $this->_stpl = new Tpl(Ounun_Dir_Tpl);
        }
	}

	/**
	 * 赋值
	 * @param string $name
	 * @param mix    $value
	 */
	public function assign($name, $value = '')
	{
		$this->_stpl->assign($name, $value);
	}

	/**
	 * 包含
	 * @param string $filename
	 */
	public function import($filename,$args=array())
	{
		$this->_stpl->import($filename,$args);
	}

    /**
     * 输出
     * @param string $filename
     */
    public function output($filename,$args=array())
    {
        $this->_stpl->output($filename,$args);
    }
}

/**
 * 路由
 * @param $route_dirs   目录路由表
 * @param $mod          目录数组
 * @param $route_hosts  主机路由表
 * @param $host         主机
 * @param $default_app_dir 默认应用
 * @return string 应用
 */
function route($route_dirs,&$mod,$route_hosts,$host,$default_app_dir)
{
    if($route_hosts && $route_hosts[$host])
    {
        return $route_hosts[$host];
    }elseif($route_dirs && $mod && $mod[0] && $route_dirs[$mod[0]])
    {
        $mod_0 = array_shift($mod);
        return $route_dirs[$mod_0];
    }
    return $default_app_dir;
}

/**
 * 世界从这里开始
 * @param aaray  $mod
 * @param string $app
 * @param bool|true $is_route_dir $app这个参数是应用ID还是应用所在目录
 */
function start($mod,$app,$is_route_dir=true)
{
    /** 重定义头 */
    header('X-Powered-By: Ounun.org');

    if($is_route_dir)
    {
        /** 应用名称 */
        define('App_Name',           	crc32($app));
        /** 应用目录 */
        define('Dir_App',           	$app);
        /** Libs目录 **/
        define('Dir_Libs',        	    \Dir_App . 'libs/');
        /** 模块所在目录 */
        define('Ounun_Dir_Module', 	    \Dir_App . 'module/');
    }else
    {
        /** 应用名称 */
        define('App_Name',           	$app);
        /** 应用目录 */
        define('Dir_App',           	\Dir_Root. 'app.'.$app.'/');
        /** Libs目录 **/
        define('Dir_Libs',        	    \Dir_App . 'libs/');
        /** 模块所在目录 */
        define('Ounun_Dir_Module', 	    \Dir_App . 'module/');
        /** 加载libs/scfg.{$app}.ini.php文件 */
        $filename   = Dir_Libs . "scfg.{$app}.ini.php";
        if(file_exists($filename))
        {
            require $filename;
        }
    } // end if($is_route_dir)

    /** 模板存放目录 */
    define('Ounun_Dir_Tpl', 	    \Const_Mobile_Edition?\Dir_App . 'tpl.mobile/':\Dir_App . 'tpl.pc/');

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
                $mod	  = array(Ounun_Default_Method);
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
                        $mod	   = array(Ounun_Default_Method);
                    }
                }else
                {
                    $filename       = Ounun_Dir_Module . $mod[0].'/'.Ounun_Default_Module.'.class.php';
                    if(file_exists(Ounun_Dir_Module . $mod[0]) && file_exists($filename))
                    {
                        $module	    = $mod[0].'\\'.Ounun_Default_Module;
                        array_shift($mod);
                    }else
                    {
                        // 默认模块
                        $module		= Ounun_Default_Module;
                        $filename 	= Ounun_Dir_Module . $module . '.class.php';
                    }
                }
            }else
            {
                $filename = Ounun_Dir_Module . $mod[0].'/'.Ounun_Default_Module.'.class.php';
                // exit($filename);
                if(file_exists($filename))
                {
                    $module		= $mod[0].'\\'.Ounun_Default_Module;
                    $mod	    = array(Ounun_Default_Method);
                    // array_shift($mod);
                }else
                {
                    // 默认模块
                    // $mod	    = array(Ounun_Default_Method);
                    $module		= Ounun_Default_Module;
                    $filename 	= Ounun_Dir_Module . $module . '.class.php';
                }
            }
        } // end Ounun_Dir_Module . $mod[0] . '.class.php';
	}
	else
	{
	    // 默认模块 与 默认方法
		$mod				= array(Ounun_Default_Method);
		$module				= Ounun_Default_Module;
		$filename 			= Ounun_Dir_Module . $module . '.class.php';
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




