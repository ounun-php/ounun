<?php 
namespace ounun;

class Http
{	
	/**
	 * @param $fun  : 方法
	 * @param $data : 数据
	 */	
	public static function Erlang($mod,$fun,$data="[]",$host="127.0.0.1",$port=18443)
	{
		$host 	= "http://{$host}:{$port}/";
		//echo $host;
		$model 	= "{{$mod},{$fun},{$data}}";
		return self::Post($host,$model, array(), 600);
	}
    /**
     * Post数据
     *
     * @param string $url
     * @param array $data
     * @param array $cookie
     * @param int $timeout
     * @return array
     */
    public static function Post($url, $data = array(), $cookie = array(), $timeout = 3)
    {
        $info = parse_url($url);
        $host = $info['host'];
        $page = $info['path'] . ($info['query']?'?' . $info['query']:'');
        $port = $info['port']?$info['port']:80;
        return self::async('POST', $host, $page, $port, $data, $cookie, $timeout);
    }

    /**
     * Get数据
     *
     * @param string $url
     * @param array $cookie
     * @param int $timeout
     * @return array
     */
    static public function Get($url, $cookie, $timeout = 3)
    {
        $info = parse_url($url);
        $host = $info['host'];
        $page = $info['path'] . ($info['query']?'?' . $info['query']:'');
        $port = $info['port']?$info['port']:80;
        return self::async('GET', $host, $page, $port, null, $cookie, $timeout);
    }

    /**
     * 异步连接
     *
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
        @$fp = fsockopen($host, $port, $errno, $errstr, $timeout);
        if(!$fp)
        {
            return array(false, '提示:无法连接!');
        }
        $stream = array();
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
}
?>