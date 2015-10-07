<?php
/** 命名空间 */
namespace sdk\wole;
/**
 * Api Abstract class
 * 
 * @abstract
 * @package 
 * @copyright 56.com
 * @author Louis Li <email:zixing.li@renren-inc.com;QQ:838431609> 
 */
abstract class ApiAbstract
{
    const CONNECT_TIMEOUT = 5;
    const READ_TIMEOUT    = 5;

	protected $conf; 
	/**
	* 应用appkey
	*/
	protected $appkey; 
	/**
	* 应用secret  
	*/
	protected $secret;
	/**
	* 接口访问host
	*/
	protected $domain;
	/**
	* 用户授权access_token
	*/
	protected $access_token;

	public function __construct()
    {

        $this->init();

		if(empty($this->appkey) || empty($this->secret))
        {
			try
            {
				throw new Exception("appkey or secret cannot be empty!");
			} catch(Exception $e)
            {
				echo $e->getMessage();
			}
		}
	}

    /**
     * @name init 
     * @todo init developer mode,appkey,secret,api domain 
     * @author Louis 
     * 
     * @access private
     * @return void
     */
    private function init()
    {
        $this->conf = include 'conf.php';

        //init developer mode
        if ($this->conf['is_developer'])
        {
            ini_set('display_errors',true);
            error_reporting(E_ALL & ~E_DEPRECATED);
        }

		$this->appkey = $this->conf['appkey'];
		$this->secret = $this->conf['secret'];
		$this->domain = $this->conf['domain'];
		$this->access_token = $this->conf['token'];
    }

    /**
     * @name setConf 
     * @todo  
     * @author Louis 
     * 
     * @param array $conf 
     * @access public
     * @return array
     */
    public function setConf($conf)
    {
		$this->appkey = $conf['appkey'];
		$this->secret = $conf['secret'];
        return $this;
    }

	/**
	* @description GET 方法
	* 
	* @access private
	* @param mixed $url
	* @param array $params
	* @return json
	*/
	protected function getHttp($url,$params=array())
    {
		$url = $url.'?'.self::signRequest($params);
		return self::httpCall($url);
	}

	/**
	* @description  POST 方法
	* 
	* @access private
	* @param mixed $url
	* @param mixed $params
	* @return json
	*/
	public function postHttp($url,$params)
    {
		return self::httpCall($url,self::signRequest($params),'post');
	}

	/**
	* @description  curl method,post方法params字符串的位置不同于get
	* 
	* @access public
	* @param mixed $url
	* @param string $params
	* @param string $method
	* @param mixed $connectTimeout
	* @param mixed $readTimeout
	* @return json
	*/
	public function httpCall($url ,$params = '',$method = 'get', $connectTimeout = self::CONNECT_TIMEOUT,$readTimeout = self::READ_TIMEOUT) {

        if ($this->conf['print_request_params'])
        {
            var_dump('url:'.$url,'params:'.$params);
        }

		$result = "";
		if (function_exists('curl_init'))
        {
			$timeout = $connectTimeout + $readTimeout;
			// Use CURL if installed...
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			if(strtolower($method)==='post')
            {
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
			}
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $connectTimeout);
			curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_USERAGENT, '56.com API PHP5 Client 1.1 (curl) ' . phpversion());
			$result = curl_exec($ch);
		} else
        {
			if(isset($params) and $params)
            {
				$url = $url."?".http_build_query($params);
			}
		    // Non-CURL based version...
			$ctx = stream_context_create(
				array(  
					'http' => array(  
						'timeout' => 5 /** 设置一个超时时间，单位为秒  */
					)  
				)  
			);  
			$result = file_get_contents($url, 0, $ctx);
		}
		return $result;
	}

	/**
	* @description 签名方法实现，并构造一个参数串
	* 
	* @access private
	* @param mixed $params
	* @return void
	*/
	public function signRequest($params)
    {
        if ($this->conf['print_request_params'])
        {
            var_dump($params);
        }
        if(isset($params['useToken']) && $params['useToken'])
        {
			$params['access_token']=$this->access_token;
		}
		$keys   = self::urlencodeRfc3986(array_keys($params));
		$values = self::urlencodeRfc3986(array_values($params));
		if($keys and $values)
        {
			$params = array_combine($keys,$values);
		}else
        {
			try
            {
				throw new Exception("signRequest need params exits!");
			} catch(Exception $e)
            {
				echo $e->getMessage();
			}
		}
		/**
		* 先去除系统级参数
		*/
		unset($params['appkey']);
		unset($params['ts']); 
		ksort($params);
		/**
		* 第一轮md5字符串
		* */	
		$req   =  md5(http_build_query($params));
		$ts    =  time();/**当次请求的时间戳**/
		/**第二轮md5字符串,得到最后的签名变量,注意里面的顺序不可以改变否则结果错误!**/
		$params['sign'] = md5($req.'#'.$this->appkey.'#'.$this->secret.'#'.$ts);
		$params['appkey']=$this->appkey;
		$params['ts']=$ts;

		return http_build_query($params);
	}

	/**
	* @description 转码异常字符
	* 
	* @access public
	* @param mixed $input
	* @return void
	*/
	public static function urlencodeRfc3986($input)
    {
		if (is_array($input))
        {
			return array_map( array('self', 'urlencodeRfc3986') , $input );
		}else if( is_scalar($input))
        {
			return str_replace( '+' , ' ' , str_replace( '%7E' , '~' , rawurlencode($input)));
		}else
        {
			return '';
		}
	}
}
