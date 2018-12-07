<?php
/**
 * 返回基类
 * Class Ret
 * @package \
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
     * @var mixed 返回数据
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
 * @param string $url  URL
 * @param array $data  数据
 * @param array $exts  要替换的数据
 * @param array $skip  忽略的数据 如:page
 * @return string
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
 * @param $uri string
 * @return array
 */
function url_to_mod(string $uri):array
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
 * @param  $url_original  string     网址
 * @param  $ext_req       bool       网址可否带参加数
 * @param  $domain        string     是否捡查 域名
 */
function url_check(string $url_original = "",bool $ext_req = true,string $domain = '')
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
        // $domain  = $_SERVER['HTTP_HOST'];
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
function json_encode_unescaped($data):string
{
    return \json_encode($data,JSON_UNESCAPED_UNICODE);
}

/**
 * 对 json格式的字符串进行解码
 * @param string $json_string
 * @return mixed
 */
function json_decode_array(string $json_string)
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
 * @param  $id int to encode
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
 * @param  $string string 字符串
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
 *
 *
 * @param string $msg
 * @param boolean $outer
 * @return string
 */
/**
 * 彈出alert對話框
 * @param string $msg
 * @param bool $outer
 * @param bool $meta
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
 *
 * @param string $msg
 * @param bool $close
 */
function msg_close(string $msg,bool $close=false):void
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
function data(string $data_mod,string $data_dir)
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
function error404(string $msg=''):void
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
            <!-- '.scfg::$app.' -->
            <!-- a padding to disable MSIE and Chrome friendly error page -->
            <!-- a padding to disable MSIE and Chrome friendly error page -->
            <!-- a padding to disable MSIE and Chrome friendly error page -->
            <!-- a padding to disable MSIE and Chrome friendly error page -->
            <!-- a padding to disable MSIE and Chrome friendly error page -->'."\n");
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
    return urlencode(mb_strtolower(sanitize($string, false)));
}

/**
 * Filter a valid UTF-8 string to be file name safe.
 *
 * @param string $string to filter
 * @return string
 */
function sanitize_filename(string $string):string
{
    return sanitize($string, false);
}


