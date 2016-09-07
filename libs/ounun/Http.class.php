<?php 
namespace ounun;

class Http
{	
	/**
	 * @param $fun  : 方法
	 * @param $data : 数据
	 */	
	public static function erlang($mod,$fun,$data="[]",$host="127.0.0.1",$port=18443)
	{
		$host 	= "http://{$host}:{$port}/";
		//echo $host;
		$model 	= "{{$mod},{$fun},{$data}}";
		return self::post($host,$model, array(), 600);
	}

    /**
     * Post数据
     * @param string $url
     * @param array $data
     * @param array $cookie
     * @param int $timeout
     * @return array
     */
    public static function post($url, $data = array(), $cookie = array(), $timeout = 3)
    {
        $info = parse_url($url);
        $host = $info['host'];
        $page = $info['path'] . ($info['query']?'?' . $info['query']:'');
        $port = $info['port']?$info['port']:80;
        return self::async('POST', $host, $page, $port, $data, $cookie, $timeout);
    }

    /**
     * Get数据
     * @param string $url
     * @param array $cookie
     * @param int $timeout
     * @return array
     */
    static public function get($url, $cookie, $timeout = 3)
    {
        $info = parse_url($url);
        $host = $info['host'];
        $page = $info['path'] . ($info['query']?'?' . $info['query']:'');
        $port = $info['port']?$info['port']:80;
        return self::async('GET', $host, $page, $port, null, $cookie, $timeout);
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
    private static function async($type, $host, $page, $port=80, $data = array(), $cookie = array(), $timeout = 3)
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
        //echo "\$host:$host, \$port:$port, \$errno:$errno, \$errstr:$errstr, \$timeout:$timeout";
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
    public static function post_curl($url,$data_json)
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
    public static function get_contents($url)
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
}
?>