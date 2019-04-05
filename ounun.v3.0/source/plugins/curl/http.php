<?php

namespace plugins\curl;

class http
{
    /**
     * post数据
     * @param string $url
     * @param array $data
     * @param array $cookie
     * @param int $timeout
     * @return array
     */
    static public function stream_post(string $url, $data = array(), $cookie = array(), $timeout = 3)
    {
        $info = parse_url($url);
        $host = $info['host'];
        $page = $info['path'] . ($info['query'] ? '?' . $info['query'] : '');
        $port = $info['port'] ? $info['port'] : 80;
        return self::stream('POST', $host, $page, $port, $data, $cookie, $timeout);
    }

    /**
     * get数据
     * @param string $url
     * @param array $cookie
     * @param int $timeout
     * @return array
     */
    static public function stream_get($url, $cookie, $timeout = 3)
    {
        $info = parse_url($url);
        $host = $info['host'];
        $page = $info['path'] . ($info['query'] ? '?' . $info['query'] : '');
        $port = $info['port'] ? $info['port'] : 80;
        return self::stream('GET', $host, $page, $port, null, $cookie, $timeout);
    }

    /**
     * 异步连接
     * @param string $type
     * @param string $host
     * @param string $page
     * @param int $port
     * @param array $data
     * @param array $cookie
     * @param int $timeout
     * @return array
     */
    private static function stream($type, $host, $page, $port = 80, $data = array(), $cookie = array(), $timeout = 3)
    {
        $type = $type == 'POST' ? 'POST' : 'GET';
        $errno = $errstr = null;
        $content = array();
        if ($type == 'POST' && $data) {
            if (is_array($data)) {
                foreach ($data as $k => $v)
                    $content[] = $k . "=" . rawurlencode($v);
                $content = implode("&", $content);
            } else {
                $content = $data;
            }
        }
        // echo "\$host:$host, \$port:$port, \$errno:$errno, \$errstr:$errstr, \$timeout:$timeout";
        $fp = fsockopen($host, $port, $errno, $errstr, $timeout);
        if (!$fp) {
            return array(false, '提示:无法连接!');
        }
        $stream = array();
        $stream[] = "{$type} {$page} HTTP/1.0";
        $stream[] = "Host: {$host}";

        if ($cookie && is_array($cookie)) {
            $tmp = array();
            foreach ($cookie as $k => $v) {
                $tmp[] = "{$k}={$v}";
            }
            $stream[] = 'Cookie:' . implode('; ', $tmp);
        }

        if ($content && $type == 'POST') {
            $stream[] = "Content-Type: application/x-www-form-urlencoded";
            $stream[] = "Content-Length: " . strlen($content);

            $stream = implode("\r\n", $stream) . "\r\n\r\n" . $content;
        } else {
            $stream = implode("\r\n", $stream) . "\r\n\r\n";
        }

        fwrite($fp, $stream);
        stream_set_timeout($fp, $timeout);
        $res = stream_get_contents($fp);
        $info = stream_get_meta_data($fp);
        fclose($fp);
        if ($info['timed_out']) {
            return array(false, '提示:连接超时');
        } else {
            return array(true, substr(strstr($res, "\r\n\r\n"), 4));
        }
    }

    /**
     * @param $url
     * @param $referer
     * @return bool|string
     */
    public static function file_get_contents(string $url, string $referer = '')
    {
        $referer = $referer ? $referer : $url;
        $opts = [
            'http' => [
                'method' => "GET",
                'header' => "Accept-Language: zh-CN,zh;q=0.9,en;q=0.8\r\n" .
                    "User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/69.0.3497.92 Safari/537.36\r\n" .
                    "Referer: {$referer}\r\n",
            ],
            "ssl" => [
                // "allow_self_signed" => false ,
                "verify_peer_name" => false,
                // "verify_peer"       => false,
                "allow_self_signed" => false,
                "verify_peer" => false,
            ],
        ];
        $context = stream_context_create($opts);
        return file_get_contents($url, false, $context);

    }


    /**
     * URL请求
     * @param $url
     * @return string
     */
    static public function curl_get_ssl($url, $referer, $timeout = '10')
    {
        if (function_exists('curl_init')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/35.0.1916.153 Safari/536.35'); // 模拟用户使用的浏览器
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_AUTOREFERER, true);
            curl_setopt($ch, CURLOPT_REFERER, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            curl_close($ch);
        } elseif (version_compare(PHP_VERSION, '5.0.0') >= 0) {
            $opts = ['http' => ['header' => "Referer:{$referer}"]];
            $result = file_get_contents($url, false, stream_context_create($opts));
        } else {
            $result = file_get_contents($url);
        }
        return $result;
    }


    static public function http_request_ssl($url, $referer, $timeout = 30, $header = array())
    {
//        $ch = curl_init();
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//        curl_setopt($ch, CURLOPT_HEADER, true);
//        curl_setopt($ch, CURLOPT_URL, $url);
//        curl_setopt($ch, CURLOPT_REFERER,        $referer);
//        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
//        if (!empty($header)) {
//            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
//        }
//        $data = curl_exec($ch);
//        list($header, $data) = explode("\r\n\r\n", $data);
//        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
//        if ($http_code == 301 || $http_code == 302)
//        {
//            $matches = array();
//            preg_match('/Location:(.*?)\n/', $header, $matches);
//            $url = trim(array_pop($matches));
//            curl_setopt($ch, CURLOPT_URL, $url);
//            curl_setopt($ch, CURLOPT_HEADER, false);
//            $data = curl_exec($ch);
//        }
//
//        if ($data == false)
//        {
//            curl_close($ch);
//        }
//        @curl_close($ch);
//        return $data;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_REFERER, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;

    }

    /**
     * @param $url
     * @param $referer
     * @param $data
     * @return bool|string
     */
    public static function file_post_contents($url, $referer, $data)
    {
        $content = http_build_query($data);
        $opts = [
            'http' => [
                'method' => "POST",
                'header' => "Content-type: application/x-www-form-urlencoded\r\n" .
                    "Content-Length: " . strlen($content) . "\r\n" .
                    "Accept-Language: zh-CN,zh;q=0.9,en;q=0.8\r\n" .
                    "User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/69.0.3497.92 Safari/537.36\r\n" .
                    "Referer: {$referer}\r\n",
                'content' => $content
            ],
            "ssl" => ["allow_self_signed" => true, "verify_peer" => false,],
        ];
        $context = stream_context_create($opts);
        return file_get_contents($url, false, $context);
    }


    /**
     * @param string $url
     * @param int $loop_max
     * @param int $file_mini_size
     * @return bool|string
     */
    public static function file_get_contents_loop(string $url, int $loop_max = 3, int $file_mini_size = 512)
    {
        $do = $loop_max;
        do {
            $do--;
            $c = self::file_get_contents($url, $url);
            if ($c && strlen($c) > $file_mini_size) {
                $do = 0;
                return $c;
            }
            sleep(1);
        } while ($do);
        return '';
    }

    /**
     * 获取网络文件，并保存
     * @param string $url
     * @param string $file_save
     * @param string $referer
     * @param int $loop_max
     * @param int $file_mini_size
     * @param int $seconds
     * @return bool|int
     */
    public static function file_get_put(string $url, string $file_save, string $referer = '', int $loop_max = 5, int $file_mini_size = 1024, int $seconds = 1)
    {
        $referer = $referer ? $referer : $url;
        $do = $loop_max;
        do {
            $do--;
            $c = self::file_get_contents($url, $referer);
            if ($c && strlen($c) > $file_mini_size) {
                $do = 0;
                return file_put_contents($file_save, $c);
            }
            if ($seconds) {
                sleep($seconds);
            }
        } while ($do);

        return false;
    }


    /**
     * 以get方式提交请求
     * @param $url
     * @return bool|mixed
     */
    static public function http_get($url)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSLVERSION, 1);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        list($content, $status) = array(curl_exec($curl), curl_getinfo($curl), curl_close($curl));
        return (intval($status["http_code"]) === 200) ? $content : false;
    }

    /**
     * 以post方式提交请求
     * @param string $url
     * @param array|string $data
     * @return bool|mixed
     */
    static public function http_post($url, $data)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, self::_build_post($data));
        list($content, $status) = array(curl_exec($curl), curl_getinfo($curl), curl_close($curl));
        return (intval($status["http_code"]) === 200) ? $content : false;
    }

    /**
     * 使用证书，以post方式提交xml到对应的接口url
     * @param string $url POST提交的内容
     * @param array $data 请求的地址
     * @param string $ssl_cer 证书Cer路径 | 证书内容
     * @param string $ssl_key 证书Key路径 | 证书内容
     * @param int $second 设置请求超时时间
     * @return bool|mixed
     */
    static public function https_post($url, $data, $ssl_cer = null, $ssl_key = null, $second = 30)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_TIMEOUT, $second);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        if (!is_null($ssl_cer) && file_exists($ssl_cer) && is_file($ssl_cer)) {
            curl_setopt($curl, CURLOPT_SSLCERTTYPE, 'PEM');
            curl_setopt($curl, CURLOPT_SSLCERT, $ssl_cer);
        }
        if (!is_null($ssl_key) && file_exists($ssl_key) && is_file($ssl_key)) {
            curl_setopt($curl, CURLOPT_SSLKEYTYPE, 'PEM');
            curl_setopt($curl, CURLOPT_SSLKEY, $ssl_key);
        }
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, self::_build_post($data));
        list($content, $status) = array(curl_exec($curl), curl_getinfo($curl), curl_close($curl));
        return (intval($status["http_code"]) === 200) ? $content : false;
    }

    /**
     * POST数据过滤处理
     * @param array $data
     * @return array
     */
    static private function _build_post(&$data)
    {
        if (is_array($data)) {
            foreach ($data as &$value) {
                if (is_string($value) && $value[0] === '@' && class_exists('CURLFile', false)) {
                    $filename = realpath(trim($value, '@'));
                    file_exists($filename) && $value = new \CURLFile($filename);
                }
            }
        }
        return $data;
    }
}
