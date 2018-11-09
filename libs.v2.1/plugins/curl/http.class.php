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
        $page = $info['path'] . ($info['query']?'?' . $info['query']:'');
        $port = $info['port']?$info['port']:80;
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
        $page = $info['path'] . ($info['query']?'?' . $info['query']:'');
        $port = $info['port']?$info['port']:80;
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
    private static function stream($type, $host, $page, $port=80, $data = array(), $cookie = array(), $timeout = 3)
    {
        $type 		= $type == 'POST'?'POST':'GET';
        $errno 		= $errstr = null;
        $content 	= array();
        if($type == 'POST' && $data)
        {
        	if(is_array($data))
        	{
        		foreach ($data as $k=>$v)
	                $content[] = $k . "=" . rawurlencode($v);
	            $content = implode("&", $content);
        	}
        	else
            {
                $content = $data;
            }
        }
        // echo "\$host:$host, \$port:$port, \$errno:$errno, \$errstr:$errstr, \$timeout:$timeout";
        $fp = fsockopen($host, $port, $errno, $errstr, $timeout);
        if(!$fp)
        {
            return array(false, '提示:无法连接!');
        }
        $stream   = array();
        $stream[] = "{$type} {$page} HTTP/1.0";
        $stream[] = "Host: {$host}";
        
        if($cookie && is_array($cookie))
        {
        	$tmp = array();
        	foreach ($cookie as $k=>$v)
        	{
        		$tmp[] = "{$k}={$v}";
        	}
        	$stream[] = 'Cookie:'.implode('; ', $tmp);
        }
        
        if($content && $type == 'POST')
        {
        	$stream[] = "Content-Type: application/x-www-form-urlencoded";
        	$stream[] = "Content-Length: " . strlen($content);
        	 
        	$stream		= implode("\r\n", $stream)."\r\n\r\n".$content;
        }else
        {
            $stream		= implode("\r\n", $stream)."\r\n\r\n";
        }

        fwrite($fp, $stream);
        stream_set_timeout($fp, $timeout);
        $res  = stream_get_contents($fp);
        $info = stream_get_meta_data($fp);
        fclose($fp);
        if($info['timed_out'])
        {
            return array(false, '提示:连接超时');
        }
        else
        {
            return array(true, substr(strstr($res, "\r\n\r\n"), 4));
        }
    }

    /**
     * curl
     * @param string $url
     * @param string $data_json
     * @return mixed
     */
    static public function curl_post($url,$data_json)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER,         0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST,           1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,     $data_json);
        curl_setopt($ch, CURLOPT_HTTPHEADER,    ['Content-Type:application/json','Content-Length: '.strlen($data_json)]);
        curl_setopt($ch, CURLOPT_ENCODING,       '');
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
        curl_setopt($ch, CURLOPT_TIMEOUT,        60);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }

    /**
     * get_contents
     * 服务器通过get请求获得内容
     * @param string $url       请求的url,拼接后的
     * @return string           请求返回的内容
     */
    static public function curl_get($url)
    {
        if (ini_get("allow_url_fopen") == "1")
        {
            $response = file_get_contents($url);
        }else
        {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_URL, $url);
            $response =  curl_exec($ch);
            curl_close($ch);
        }
        // 请求为空
        if(empty($response))
        {
            trigger_error("ERROR! 可能是服务器无法请求http(s)协议.", E_USER_ERROR);
        }
        return $response;
    }





    /**
     * @param $url
     * @param $referer
     * @param string $timeout
     * @return mixed
     */
    static public function curl_get3($url,$referer,$timeout = '10')
    {
        // 1. 初始化
        $ch = curl_init();
        // 2. 设置选项，包括URL
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/35.0.1916.153 Safari/536.35'); // 模拟用户使用的浏览器
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);    // 自动设置Referer
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_REFERER, $referer);
        // 3. 执行并获取HTML文档内容
        $info = curl_exec($ch);
        // 4. 释放curl句柄
        curl_close($ch);

        return $info;
    }

    /**
     * @param $url
     * @param $referer
     * @return bool|string
     */
    public static function file_get_contents(string $url,string $referer='')
    {
        if ('https' == substr($url,0,5))
        {
            // echo $url."\n";
            // exit();
            // $url = 'https://mm.erldoc.com/';
            // return self::http_request_ssl($url,$referer);
        }

        $referer = $referer?$referer:$url;
        $opts = [
            'http' => [
                'method'       => "GET",
                'header'       => "Accept-Language: zh-CN,zh;q=0.9,en;q=0.8\r\n".
                    "User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/69.0.3497.92 Safari/537.36\r\n".
                    "Referer: {$referer}\r\n",
            ],
            "ssl"  => [
             // "allow_self_signed" => false ,
                "verify_peer_name"  => false,
             // "verify_peer"       => false,
                "allow_self_signed" => false,
                "verify_peer"       => false,
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
    static public function curl_get_ssl($url,$referer,$timeout = '10')
    {
        if(function_exists('curl_init'))
        {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL,            $url);
            curl_setopt($ch, CURLOPT_TIMEOUT,        $timeout);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/35.0.1916.153 Safari/536.35'); // 模拟用户使用的浏览器
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_AUTOREFERER,    true);
            curl_setopt($ch, CURLOPT_REFERER,        $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            curl_close($ch);
        }elseif(version_compare(PHP_VERSION, '5.0.0')>=0)
        {
            $opts   = ['http' => ['header' => "Referer:{$referer}"]];
            $result = file_get_contents($url,false,stream_context_create($opts));
        }else
        {
            $result = file_get_contents($url);
        }
        return $result;
    }


    static public  function http_request_ssl($url,$referer,$timeout=30,$header=array())
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
    public static function file_post_contents($url,$referer,$data)
    {
        $content = http_build_query($data);
        $opts = [
            'http' => [
                'method'  => "POST",
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n".
                             "Content-Length: " . strlen($content) . "\r\n".
                             "Accept-Language: zh-CN,zh;q=0.9,en;q=0.8\r\n".
                             "User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/69.0.3497.92 Safari/537.36\r\n".
                             "Referer: {$referer}\r\n",
                'content' => $content
            ],
            "ssl"  => [ "allow_self_signed" => true ,  "verify_peer"  => false,  ],
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
    public static function file_get_contents_loop(string $url,int $loop_max=3,int $file_mini_size = 512)
    {
        $do  =  $loop_max;
        do{
            $do--;
            $c = self::file_get_contents($url,$url);
            if($c  && strlen($c) > $file_mini_size)
            {
                $do = 0;
                return $c;
            }
            sleep(1);
        }while($do);
        return '';
    }



    /**
     * 获取网络文件，并保存
     * @param string $url
     * @param string $file_save
     * @param int $mini_size
     */
    public static function file_get_put(string $url, string $file_save,string $referer='',int $loop_max = 5,int $file_mini_size = 1024,int $seconds=1)
    {
        $referer = $referer?$referer:$url;
        $do      = $loop_max;
        do{
            $do--;
            $c = self::file_get_contents($url,$referer);
            if($c  && strlen($c) > $file_mini_size)
            {
                $do = 0;
                return file_put_contents($file_save,$c);
            }
            if($seconds)
            {
                sleep($seconds);
            }
        }while($do);
    }
}