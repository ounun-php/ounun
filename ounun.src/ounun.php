<?php
/** 命名空间 */
namespace ounun;

/** ounun根目录 */
define('Ounun_Dir', 		 	    \Dir_Lib . 'ounun/');

/** 模板存放目录 */
define('Ounun_Dir_Tpl', 		    \Dir_App . 'templates/');

/** 模块所在目录 */
define('Ounun_Dir_Module', 		    \Dir_App . 'module/');

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
// echo 'Ounun_Default_Module:'.Ounun_Default_Module."<br />\n";
// echo 'Ounun_Default_Method:'.Ounun_Default_Method."<br />\n";
/**
 * 得到访客的IP
 *
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
 * 输出URL
 */
function url($url,$data)
{
	$rs = array();
	if(is_array($data))
	{
		foreach ($data as $key => $value)
			$rs[] = $key.'='.urlencode($value);
	}
	return $url.(strstr('?',$url)?'&':'?').implode('&',$rs);
}

/**
 * 得到 原生 URL(去问号后的 QUERY_STRING)
 */
function url_original($uri)
{
	$tmp = explode('?', $uri, 2);
	return $tmp[0];
}
/**
 * 通过uri得到mod
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
 * @param bool $ext_url             网址扩展参数
 * @param bool $ext_html            是否.html结束
 * @param bool $ext_req             网址可否带参加数
 * @param null $domain              是否捡查 域名
 */
function url_check($url_original="",$ext_url="",$ext_html=false,$ext_req=true,$domain=null)
{
    // URL去重
    $url_reset  = "";
    $url        = explode('?',$_SERVER['REQUEST_URI']);
    if($ext_url)
    {
        $url_tmp_pre = substr($url[0],0, strlen($url_original) );
        if($ext_html)
        {
            if($url_tmp_pre != $url_original
                || '.html' != substr($url[0],'-5',5)  )
            {
                $url_reset = "{$url_original}/{$ext_url}.html";
            }
        }else
        {
            if($url_tmp_pre != $url_original
                || '/' != substr($url[0],'-1',1)  )
            {
                $url_reset = "{$url_original}/{$ext_url}/";
            }
        }
    }else
    {
        $url_tmp = $url_original.($ext_html?'.html':'/');
        if($url_tmp != $url[0])
        {
            $url_reset = $url_tmp;
        }
    }
    // URL参数
    if($url_reset && $ext_req && $url[1])
    {
        $url_reset  = "{$url_reset}?{$url[1]}";
    }
    //echo "<br />url:",$url_reset;
    //exit();
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
 */
function go_url($url,$top=false,$head_code=302)
{
    if($top)
    {
        echo '<script type="text/javascript">' . "\n";
        echo "window.top.location.href='{$url}';\n";
        echo '</script>' . "\n";
    }
    else
    {
        if(!headers_sent())
        {
            header('Location: '.$url,null,$head_code);
        }
        else
        {
            echo '<meta http-equiv="refresh" content="0;url=' . $url . '">';
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
function msg($msg, $outer = true)
{
	$rs = "\n" . 'alert(' . Json_encode($msg) . ');' . "\n";
	if($outer)
	{
		$rs = '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />' . "\n" . '<script type="text/javascript">' . "\n" . $rs . "\n" . '</script>' . "\n";
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
                <meta http-equiv="refresh" content="3;url=/">
                <title>404 Not Found</title>
            </head>
            <body bgcolor="white">
            <center><h1>404 Not Found</h1></center>
            <hr><center><a href="/">返回网站首页</a></center>
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
 * 基类的基类
 */
class Base
{
	/**
	 * 默认方法
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
		//$default_method = $this->default_method;
		//$this->$default_method($arg[0], $method);
        error404();
	}
	/**
	 * DB 相关
	 * @param sting $key enum:member,goods,admin,msg,help
	 */
	private static $_db = array();
	/**
	 * 返回数据库连接对像
	 *
	 * @param string $key
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
	 */
	public function logs($k,$log)
	{
		if($this->debug)
		{
			$this->debug->logs($k,$log);
		}
	}
}

/**
 * 构造模块基类 *
 */
class ViewBase extends Base
{
	public function __construct($mod)
	{
        if(!$mod)
		{
			$mod = array($this->default_method);
		}
		$this->$mod [0] ( $mod );		
	}
	/**
	 * Template句柄容器
	 * @var \ounun\Tpl
	 */
	protected $_stpl;

	public function Template()
	{
		require  Ounun_Dir. 'Tpl.class.php';
		$this->_stpl = new Tpl(Ounun_Dir_Tpl);
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
	 * 输出
	 * @param string $filename
	 */
	public function import($filename)
	{
		$this->_stpl->import($filename);
	}
}
/**
 * 世界从这里开始
 */
function start($mod)
{
	// 设时区
	date_default_timezone_set('Asia/Chongqing');
	// 重定义头
	header('X-Powered-By: Ounun.org');
	// 设定 模块与方法
	if(is_array($mod) && $mod[0])
	{
        $filename         = Ounun_Dir_Module . $mod[0] . '.class.php';
        if(file_exists($filename))
        {
            $module		  = $mod[0];
            array_shift($mod);
        }
        else
        {
            if($mod[1])
            {
                $filename = Ounun_Dir_Module . "{$mod[0]}/{$mod[1]}.class.php";
                if(file_exists($filename))
                {
                    $module		    = $mod[0].'\\'.$mod[1];
                    array_shift($mod);
                    array_shift($mod);
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
                if(file_exists($filename))
                {
                    $module		= $mod[0].'\\'.Ounun_Default_Module;
                    array_shift($mod);
                }else
                {
                    // 默认模块
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




