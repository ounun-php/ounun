<?php
/** libs库文件目录 **/
defined('Dir_Ounun') || define('Dir_Ounun', __DIR__ . '/');
/** libs目录 **/
defined('Dir_Vendor') || define('Dir_Vendor', Dir_Root . 'vendor/');
/** data目录 **/
defined('Dir_Extend') || define('Dir_Extend', Dir_Root . 'extend/');
/** template目录 **/
defined('Dir_Template') || define('Dir_Template', Dir_Root . 'template/');
/** data目录 **/
defined('Dir_Data') || define('Dir_Data', Dir_Root . 'data/');
/** cache目录 **/
defined('Dir_Cache') || define('Dir_Cache', Dir_Data . 'cache/');
/** app目录 **/
defined('Dir_App') || define('Dir_App', Dir_Root . 'app/');
/** Environment目录 **/
defined('Environment') || define('Environment', environment());

/**
 * 得到访客的IP
 * @return string IP
 */
function ip(): string
{
    static $hdr_ip;
    if (empty($hdr_ip)) {
        if (isset($_SERVER['HTTP_CDN_SRC_IP'])) {
            $hdr_ip = stripslashes($_SERVER['HTTP_CDN_SRC_IP']);
        } else {
            if (isset($_SERVER['HTTP_CLIENT_IP'])) {
                $hdr_ip = stripslashes($_SERVER['HTTP_CLIENT_IP']);
            } else {
                if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                    $hdr_ip = stripslashes($_SERVER['HTTP_X_FORWARDED_FOR']);
                } else {
                    $hdr_ip = stripslashes($_SERVER['REMOTE_ADDR']);
                    if (empty($hdr_ip)) {
                        $hdr_ip = '127.0.0.1';
                    }
                }
            }
        }
    }
    return $hdr_ip;
}


/**
 * 输出带参数的URL
 * @param string $url URL
 * @param array $data_query 数据
 * @param array $replace_ext 要替换的数据
 * @param array $skip 忽略的数据 如:page
 * @return string
 */
function url_build_query(string $url, array $data_query, array $replace_ext = [], array $skip = []): string
{
    $rs = [];
    if (is_array($data_query)) {
        if ($replace_ext && is_array($replace_ext)) {
            foreach ($replace_ext as $key => $value) {
                $data_query[$key] = $value;
            }
        }
        if ($skip && is_array($skip)) {
            foreach ($skip as $key => $value) {
                if ($value) {
                    if (is_array($value) && in_array($data_query[$key], $value, true)) {
                        unset($data_query[$key]);
                    } elseif ($value == $data_query[$key]) {
                        unset($data_query[$key]);
                    }
                } else {
                    unset($data_query[$key]);
                }
            }
        }
        $rs = [];
        $rs_str = '';
        foreach ($data_query as $key => $value) {
            if ('{page}' === $value) {
                $rs_str = $key . '={page}';
            } elseif (is_array($value)) {
                foreach ($value as $k2 => $v2) {
                    $rs[] = $key . '[' . $k2 . ']=' . urlencode($v2);
                }
            } elseif ($value || 0 === $value || '0' === $value) {
                $rs[] = $key . '=' . urlencode($value);
            }
        }
        // 已保正page 是最后项
        if ($rs_str) {
            $rs[] = $rs_str;
        }
    }
    $url = trim($url);
    if ($rs) {
        if ($url && strlen($url) > 1) {
            if (strpos($url, '?') === false) {
                return $url . '?' . implode('&', $rs);
            }
            return $url . '&' . implode('&', $rs);
        }
        return implode('&', $rs);
    }
    return $url;
}

/**
 * 得到 原生 URL(去问号后的 QUERY_STRING)
 * @param $uri
 * @return string URL
 */
function url_original(string $uri = ''): string
{
    if ('' == $uri) {
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
function url_to_mod(string $uri): array
{
    $uri = \explode('/', $uri, 2);
    $uri = \explode('.', urldecode($uri[1]), 2);
    $uri = \explode('/', $uri[0]);
    $mod = [];
    foreach ($uri as $v) {
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
function url_check(string $url_original = "", bool $ext_req = true, string $domain = '')
{
    // URL去重
    $url = explode('?', $_SERVER['REQUEST_URI'], 2);
    $url_reset = '';
    if (false == $ext_req && $url[1]) {
        $url_reset = $url_original;
    } elseif ($url_original != $url[0]) {
        $url_reset = $url_original;
        if ($ext_req && $url[1]) {
            $url_reset = "{$url_reset}?{$url[1]}";
        }
    }
    // echo("\$url_reset:{$url_reset} \$url_original:{$url_original}\n");
    // exit("\$domain:{$domain}\n");
    // 域名
    if ($domain && $domain != $_SERVER['HTTP_HOST']) {
        // $domain  = $_SERVER['HTTP_HOST'];
        $url_reset = $url_reset ? $url_reset : $_SERVER['REQUEST_URI'];
        $url_reset = "//{$domain}{$url_reset}";
        // exit("\$url_reset:{$url_reset} \$domain:{$domain}\n");
        go_url($url_reset, false, 301);
    } else if ($url_reset) {
        // exit("\$url_reset:{$url_reset}\n");
        go_url($url_reset, false, 301);
    }
    // exit("\$domain:{$domain}\n");
}

/**
 * @param string $url1
 * @param string $url2
 * @param string $note
 * @param bool $top
 */
function go_note(string $url1, string $url2, string $note, bool $top = false): void
{
    $top = "\t" . ($top ? 'window.top.' : '');
    $note = $note ? $note : '点击“确定”继续操作  点击“取消” 中止操作';
    echo '<script type="text/javascript">' . "\n";
    if ($url2) {
        $url1 = $top . "location.href='{$url1}';\n";
        $url2 = $top . "location.href='{$url2}';\n";
        echo 'if(window.confirm(' . json_encode($note) . ')){' . "\n" . $url1 . '}else{' . "\n" . $url2 . '}' . "\n";
    } else {
        $url1 = $top . "location.href='{$url1}';\n";
        echo 'if(window.confirm(' . json_encode($note) . ')){' . "\n" . $url1 . '};' . "\n";
    }
    echo '</script>' . "\n";
    exit();
}

/**
 * @param $url
 * @param bool $top
 * @param int $head_code
 * @param int $delay 延时跳转(单位秒)
 */
function go_url(string $url, bool $top = false, int $head_code = 302, int $delay = 0): void
{
    if ($top) {
        echo '<script type="text/javascript">' . "\n";
        echo "window.top.location.href='{$url}';\n";
        echo '</script>' . "\n";
    } else {
        if (!headers_sent() && 0 == $delay) {
            header('Location: ' . $url, null, $head_code);
        } else {
            echo '<meta http-equiv="refresh" content="' . ((int)$delay) . ';url=' . $url . '">';
        }
    }
    exit();
}

/**
 * 返回
 */
function go_back(): void
{
    echo '<script type="text/javascript">', "\n", 'window.history.go(-1);', "\n", '</script>', "\n";
    exit();
}

/**
 * @param $msg
 * @param $url
 */
function go_msg(string $msg, string $url = ''): void
{
    if ($url) {
        exit(msg($msg) . '<meta http-equiv="refresh" content="0.5;url=' . $url . '">');
    } else {
        echo msg($msg);
        go_back();
    }
}

/**
 * 返回一个错误
 * @param string $message
 * @param int $error_code
 * @param mixed $data
 * @param array $extend 延伸数据
 * @return array
 */
function error(string $message = '', int $error_code = 1, $data = null, $extend = [])
{
    $rs = ['message' => $message, 'error_code' => $error_code];
    if ($data) {
        $rs['data'] = $data;
    }
    if ($extend) {
        $rs = array_merge($extend, $rs);
    }
    return $rs;
}

/**
 * 确认是否错误 数据
 * @param $data
 * @return bool
 */
function error_is($data)
{
    if (empty($data) || !is_array($data) || !array_key_exists('error_code', $data) || (array_key_exists('error_code', $data) && $data['error_code'] == 0)) {
        return false;
    } else {
        return true;
    }
}

/**
 * 判断服务器是否是HTTPS连接
 * @return bool
 */
function https_is()
{
    if (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') {
        return true;
    } elseif (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
        return true;
    } elseif (!empty($_SERVER['HTTP_FRONT_END_HTTPS']) && strtolower($_SERVER['HTTP_FRONT_END_HTTPS']) !== 'off') {
        return true;
    }
    return false;
}

/**
 * 返回错误提示信息
 * @param $data
 * @return string
 */
function error_message($data): string
{
    return $data['message'];
}

/**
 * 返回错误代码
 * @param $data
 * @return int
 */
function error_code($data): int
{
    return $data['error_code'];
}

/**
 * @param mixed $data
 * @param string $message
 * @param array $extend 延伸数据
 * @return array
 */
function succeed($data, string $message = '', $extend = [])
{
    if ($extend) {
        return array_merge($extend, ['message' => $message, 'error_code' => 0, 'data' => $data]);
    }
    return ['message' => $message, 'error_code' => 0, 'data' => $data];
}

/**
 * 返回 数据
 * @param $data
 * @return mixed
 */
function succeed_data($data)
{
    return $data['data'];
}

/**
 * Ajax方式返回数据到客户端
 * @param mixed $data 要返回的数据
 * @param string $type AJAX返回数据格式
 * @param string $jsonp_callback
 * @param int $json_options 传递给json_encode的option参数
 */
function out($data, string $type = '', string $jsonp_callback = '', int $json_options = JSON_UNESCAPED_UNICODE)
{
    if (empty($type)) {
        $type = \ounun\mvc\c::Format_Json;
    }
    switch ($type) {
        // 返回JSON数据格式到客户端 包含状态信息
        case \ounun\mvc\c::Format_Json :
            header('Content-Type:application/json; charset=utf-8');
            exit(json_encode($data, $json_options));
        // 返回xml格式数据
        case \ounun\mvc\c::Format_Xml :
            header('Content-Type:text/xml; charset=utf-8');
            exit(\ounun\tool\data::xml_encode($data));
        // 返回JSON数据格式到客户端 包含状态信息
        case \ounun\mvc\c::Format_Jsonp:
            header('Content-Type:application/javascript; charset=utf-8');
            if (empty($jsonp_callback)) {
                $jsonp_callback = (isset($_GET['jsonp_callback']) && $_GET['jsonp_callback']) ? $_GET['jsonp_callback'] : 'jsonp_callback';
            }
            exit($jsonp_callback . '(' . json_encode($data, $json_options) . ');');
        // 返回可执行的js脚本
        case  \ounun\mvc\c::Format_JS :
        case  \ounun\mvc\c::Format_Eval :
            header('Content-Type:application/javascript; charset=utf-8');
            exit($data);
        // 返回可执行的js脚本
        // case \ounun\mvc\c::Format_Html :
        default :
            header('Content-Type:text/html; charset=utf-8');
            exit($data);
    }
}

/**
 * 获得 json字符串数据
 * @param $data
 * @return string
 */
function json_encode_unescaped($data): string
{
    return json_encode($data, JSON_UNESCAPED_UNICODE);
}

/**
 * 对 json格式的字符串进行解码
 * @param string $json_string
 * @return mixed
 */
function json_decode_array(?string $json_string)
{
    return json_decode($json_string, true);
}

/**
 * @param $string
 * @return bool|string
 */
function base58_encode($string)
{
    $alphabet = '123456789abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ';
    $base = strlen($alphabet);
    if (is_string($string) === false) {
        return false;
    }
    if (strlen($string) === 0) {
        return '';
    }
    $bytes = array_values(unpack('C*', $string));
    $decimal = $bytes[0];
    for ($i = 1, $l = count($bytes); $i < $l; $i++) {
        $decimal = bcmul($decimal, 256);
        $decimal = bcadd($decimal, $bytes[$i]);
    }
    $output = '';
    while ($decimal >= $base) {
        $div = bcdiv($decimal, $base, 0);
        $mod = bcmod($decimal, $base);
        $output .= $alphabet[$mod];
        $decimal = $div;
    }
    if ($decimal > 0) {
        $output .= $alphabet[$decimal];
    }
    $output = strrev($output);
    foreach ($bytes as $byte) {
        if ($byte === 0) {
            $output = $alphabet[0] . $output;
            continue;
        }
        break;
    }
    return (string)$output;
}

/**
 * 字附串
 * @param $base58
 * @return bool|string
 */
function base58_decode($base58)
{
    $alphabet = '123456789abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ';
    $base = strlen($alphabet);

    if (is_string($base58) === false) {
        return false;
    }
    if (strlen($base58) === 0) {
        return '';
    }
    $indexes = array_flip(str_split($alphabet));
    $chars = str_split($base58);
    foreach ($chars as $char) {
        if (isset($indexes[$char]) === false) {
            return false;
        }
    }
    $decimal = $indexes[$chars[0]];
    for ($i = 1, $l = count($chars); $i < $l; $i++) {
        $decimal = bcmul($decimal, $base);
        $decimal = bcadd($decimal, $indexes[$chars[$i]]);
    }
    $output = '';
    while ($decimal > 0) {
        $byte = bcmod($decimal, 256);
        $output = pack('C', $byte) . $output;
        $decimal = bcdiv($decimal, 256, 0);
    }
    foreach ($chars as $char) {
        if ($indexes[$char] === 0) {
            $output = "\x00" . $output;
            continue;
        }
        break;
    }
    return $output;
}

/**
 * 获得 extend数据php
 * @param string $extend_string
 * @return array|mixed
 */
function extend_decode_php(string $extend_string)
{
    $ext = [];
    if ($extend_string) {
        $ext = unserialize($extend_string);
    }
    return $ext;
}

/**
 * 获得 extend数据json
 * @param string $extend_string
 * @return array|mixed
 */
function extend_decode_json(string $extend_string)
{
    $extend = [];
    if ($extend_string) {
        $extend = json_decode($extend_string);
    }
    return $extend;
}

/**
 * 对字符串进行编码，这样可以安全地通过URL
 * @param string $string to encode
 * @return string
 */
function base64_url_encode(string $string = null): string
{
    return strtr(base64_encode($string), '+/=', '-_~');
}

/**
 * 解码一个 URL传递的字符串
 * @param string $string to decode
 * @return string
 */
function base64_url_decode(string $string = null): string
{
    return base64_decode(strtr($string, '-_~', '+/='));
}

/**
 * 编号 转 字符串
 * @param  $id int to encode
 * @return string
 */
function short_url_encode(int $id = 0): string
{
    if ($id < 10) {
        return (string)$id;
    }
    $show = '';
    while ($id > 0) {
        $s = $id % 62;
        $show = ($s > 35 ? chr($s + 61) : ($s > 9 ? chr($s + 55) : $s)) . $show;
        $id = floor($id / 62);
    }
    return $show;
}

/**
 * 字符串 转 编号
 * @param  $string string 字符串
 * @return int
 */
function short_url_decode(string $string = ''): int
{
    $p = 0;
    while ($string !== '') {
        $s = substr($string, 0, 1);
        $n = is_numeric($s) ? $s : ord($s);
        $p = $p * 62 + (($n >= 97) ? ($n - 61) : ($n >= 65 ? $n - 55 : $n));
        $string = substr($string, 1);
    }
    return $p;
}

/**
 * 彈出alert對話框
 * @param string $msg
 * @param bool $outer
 * @param bool $meta
 * @return string
 */
function msg(string $msg, bool $outer = true, $meta = true): string
{
    $rs = "\n" . 'alert(' . json_encode($msg) . ');' . "\n";
    if ($outer) {
        if ($meta) {
            $mt = '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />' . "\n";
        } else {
            $mt = '';
        }
        $rs = $mt . '<script type="text/javascript">' . "\n" . $rs . "\n" . '</script>' . "\n";
    }
    return $rs;
}

/**
 * 出错提示错
 * @param string $msg
 * @param bool $close
 */
function msg_close(string $msg, bool $close = false): void
{
    $rs = "\n" . 'alert(' . json_encode($msg) . ');' . "\n";
    $mt = '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />' . "\n";
    $rs = $mt . '<script type="text/javascript">' . "\n" . $rs . "\n" . '</script>' . "\n";
    echo $rs;
    if ($close) {
        // 本页自动关闭.
        echo '<script type="text/javascript">window.opener = null; window.open("", "_self", ""); window.close(); </script>';
    }
    exit();
}

/**
 * 获得libs Data数据
 * @param string $data_mod
 * @param string $data_dir
 * @return mixed
 */
function data(string $data_mod, string $data_dir)
{
    $filename = "{$data_dir}data.{$data_mod}.ini.php";
    if (file_exists($filename)) {
        return require $filename;
    }
    return null;
}

/**
 * HTTP缓存控制
 * @param int $expires 缓存时间 0:为不缓存 单位:s
 * @param string $etag ETag
 * @param int $LastModified 最后更新时间
 */
function expires(int $expires = 0, string $etag = '', int $LastModified = 0)
{
    if ($expires) {
        $time = time();
        header("Expires: " . gmdate("D, d M Y H:i:s", $time + $expires) . " GMT");
        header("Cache-Control: max-age=" . $expires);
        if ($LastModified) {
            header("Last-Modified: " . gmdate("D, d M Y H:i:s", $LastModified) . " GMT");
        }
        if ($etag) {
            if ($etag == $_SERVER["HTTP_IF_NONE_MATCH"]) {
                header("Etag: " . $etag, true, 304);
                exit();
            } else {
                header("Etag: " . $etag);
            }
        }
    } else {
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header("Cache-Control: no-cache, must-revalidate");
        header("Pragma: no-cache");
    }
}

/**
 * error 404
 * @param string $msg
 */
function error404(string $msg = ''): void
{
    header('HTTP/1.1 404 Not Found');
    exit('<html>
            <head>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
                <title>404 Not Found</title>
            </head>
            <body bgcolor="white">
                <div align="center">
                    <h1>404 Not Found' . ($msg ? '(' . $msg . ')' : '') . '</h1>
                </div>
                <hr>
                <div align="center"><a href="/">返回网站首页</a></div>
            </body>
            </html>
            <!-- a padding to disable MSIE and Chrome friendly error page -->
            <!-- ' . \ounun\config::$app_name . ' -->
            <!-- a padding to disable MSIE and Chrome friendly error page -->
            <!-- a padding to disable MSIE and Chrome friendly error page -->
            <!-- a padding to disable MSIE and Chrome friendly error page -->
            <!-- a padding to disable MSIE and Chrome friendly error page -->
            <!-- a padding to disable MSIE and Chrome friendly error page -->' . "\n");
}

/**
 * @param $delimiters
 * @param $string
 * @return array
 */
function explodes(string $delimiters, string $string)
{
    $ready = \str_replace($delimiters, $delimiters[0], $string);
    $launch = \explode($delimiters[0], $ready);
    return $launch;
}

/**
 * Convert special characters to HTML safe entities.
 * 特殊字符转换成 HTML安全格式。
 * @param string $string to encode
 * @return string
 */
function safe(string $string): string
{
    return htmlspecialchars($string, ENT_QUOTES, 'utf-8');
}

/**
 * Filter a valid UTF-8 string so that it contains only words, numbers,
 * dashes, underscores, periods, and spaces - all of which are safe
 * characters to use in file names, URI, XML, JSON, and (X)HTML.
 * @param string $string to clean
 * @param bool $spaces TRUE to allow spaces
 * @return string
 */
function sanitize(string $string, bool $spaces = true): string
{
    $search = [
        '/[^\w\-\. ]+/u',   // Remove non safe characters
        '/\s\s+/',          // Remove extra whitespace
        '/\.\.+/',
        '/--+/',
        '/__+/'             // Remove duplicate symbols
    ];
    $string = preg_replace($search, [' ', ' ', '.', '-', '_'], $string);
    if (!$spaces) {
        $string = preg_replace('/--+/', '-', str_replace(' ', '-', $string));
    }
    return trim($string, '-._ ');
}

/**
 * Create a SEO friendly URL string from a valid UTF-8 string.
 * @param string $string to filter
 * @return string
 */
function sanitize_url(string $string): string
{
    return urlencode(mb_strtolower(sanitize($string, false)));
}

/**
 * Filter a valid UTF-8 string to be file name safe.
 * @param string $string to filter
 * @return string
 */
function sanitize_filename(string $string): string
{
    return sanitize($string, false);
}

/**
 * 当前开发环境
 * @return string '','2','-dev'
 */
function environment()
{
    if (isset($GLOBALS['_environment_'])) {
        return $GLOBALS['_environment_'];
    }
    $env_file = isset($GLOBALS['_environment_file_']) && $GLOBALS['_environment_file_'] ? $GLOBALS['_environment_file_'] : '/www/wwwroot/release.txt';
    if (is_file($env_file)) {
        $GLOBALS['_environment_'] = '';
    } else {
        $env_file = Dir_Root . 'environment.txt';
        $GLOBALS['_environment_'] = (is_file($env_file) && filesize($env_file) >= 1) ? trim(file_get_contents($env_file)) : '';
    }
    return $GLOBALS['_environment_'];
}


/**
 * 构造模块基类
 * Class ViewBase
 * @package ounun
 */
abstract class v
{
    /** @var \ounun\mvc\model\cms cms */
    public static $cms;

    /** @var \ounun\pdo DB */
    public static $db_v;

    /** @var int cache_html_time */
    public static $cache_html_time = 2678400; // 31天

    /** @var bool html_trim */
    public static $cache_html_trim = true;

    /** @var \ounun\cache\html cache_html */
    public static $cache_html;

    /**
     * Cache
     * @param $key
     */
    public function cache_html($key)
    {
        if ('' == Environment && \ounun\config::$global['cache_html']) {
            $cfg = \ounun\config::$global['cache_html'];
            $cfg['mod'] = 'html_' . \ounun\config::$app_name . \ounun\config::$tpl_style;
            $key2 = \ounun\config::$app_name . '_' . \ounun\config::$tpl_style . '_' . $key;
            static::$cache_html = new \ounun\cache\html($cfg, $key2, static::$cache_html_time, static::$cache_html_trim, '' != Environment);
            static::$cache_html->run(true);
        }
    }

    /**
     * 是否马上输出cache
     * @param bool $output
     */
    public function cache_html_stop(bool $output)
    {
        if (static::$cache_html) {
            static::$cache_html->stop($output);
            static::$tpl->replace();
        }
    }

    /** @var  \ounun\template  Template句柄容器 */
    public static $tpl;

    /**
     * (兼容)返回一个 模板文件地址(绝对目录,相对root)
     * @param string $filename
     * @return string
     */
    static public function tpl_fixed(string $filename): string
    {
        return static::$tpl->tpl_fixed($filename);
    }

    /**
     * (兼容)返回一个 模板文件地址(相对目录)
     * @param string $filename
     * @return string
     */
    static public function tpl_curr(string $filename): string
    {
        return static::$tpl->tpl_curr($filename);
    }

    /** @var \ounun\debug 调试 相关 */
    public static $debug;

    /**
     * 调试日志
     * @param $k
     * @param $log
     */
    public function debug_logs(string $k, $log)
    {
        if (static::$debug) {
            static::$debug->logs($k, $log);
        }
    }

    /**
     * 停止 调试
     */
    public function debug_stop()
    {
        if (static::$debug) {
            static::$debug->stop();
        }
    }

    /**
     * ounun_view constructor.
     * @param $mod
     */
    public function __construct($mod)
    {
        if (!$mod) {
            $mod = [\ounun\config::def_method];
        }
        $method = $mod[0];
        \ounun\config::$view = $this;
        $this->$method($mod);
    }

    /** @var string 当前面页(网址) */
    public $page_url = '';

    /** @var string 当前面页(文件名) */
    public $page_file = '';

    /**
     * 初始化Page
     * @param string $page_file
     * @param bool $is_cache_html
     * @param bool $ext_req
     * @param string $domain
     * @param int $cache_html_time
     * @param bool $cache_html_trim
     */
    public function init_page(string $page_file = '', bool $is_cache_html = true, bool $ext_req = true, string $domain = '', int $cache_html_time = 0, bool $cache_html_trim = true)
    {
        // url_check
        $this->page_file = $page_file;
        $this->page_url = \ounun\config::url_page($this->page_file);

        url_check($this->page_url, $ext_req, $domain);

        // cache_html
        static::$cache_html_trim = '' == Environment ? $cache_html_trim : false;
        if ($is_cache_html) {
            static::$cache_html_time = $cache_html_time > 300 ? $cache_html_time : static::$cache_html_time;
            $this->cache_html($this->page_url);
        }

        // cms
        $cls = \ounun\config::$app_cms_classname;
        // template
        if (empty(static::$tpl)) {
            static::$tpl = new \ounun\template(\ounun\config::$tpl_style, \ounun\config::$tpl_default, static::$cache_html_trim);
        }

        // db
        if (empty(static::$db_v)) {
            static::$db_v = \ounun\pdo::instance(\ounun\config::database_default_get());
        }
        static::$cms = new $cls(static::$db_v);
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
        if (file_exists(\ounun\config::$dir_app . 'robots.txt')) {
            readfile(\ounun\config::$dir_app . 'robots.txt');
        } else {
            exit("User-agent: *\nDisallow:");
        }
    }

    /**
     * /favicon.ico
     */
    public function favicon($mod)
    {
        go_url(\ounun\config::$url_static . 'favicon.ico', false, 301);
    }

    /**
     * 没定的方法
     * @param string $method
     * @param String $arguments
     */
    public function __call($method, $arguments)
    {
        header('HTTP/1.1 404 Not Found');
        if (empty(static::$debug)) {
            static::$debug = new \ounun\debug(\ounun\config::$dir_data . 'logs/error_404_' . date('Ymd') . '.txt', false, false, false, true);
        }
        error404("\$method:{$method} \$args:" . json_encode($arguments) . "");
    }
}
