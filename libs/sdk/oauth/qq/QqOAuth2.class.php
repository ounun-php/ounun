<?php
/**
 * Created by PhpStorm.
 * User: dreamxyp
 * Date: 14-9-20
 * Time: 下午3:58
 */
namespace sdk\oauth\qq;

class QqOAuth2
{
    const VERSION               = "2.0";
    const GET_AUTH_CODE_URL     = "https://graph.qq.com/oauth2.0/authorize";
    const GET_ACCESS_TOKEN_URL  = "https://graph.qq.com/oauth2.0/token";
    const GET_OPENID_URL        = "https://graph.qq.com/oauth2.0/me";

    private $_appid;
    private $_appkey;
    private $_callback;
    private $_scope;
    /** @var \sdk\oauth\Session */
    private $_session;
    private $_api_map;
    /** 构造函数 */
    public function __construct($session_handle,$appid,$appkey,$callback,$scope)
    {
        $this->_session  = $session_handle;
        // $this->_session->start();  // 已移到外面了

        $this->_appid    = $appid;
        $this->_appkey   = $appkey;
        $this->_callback = $callback;
        $this->_scope    = $scope;

        $this->_api_map  = null;
    }

    public function login()
    {
        //-------生成唯一随机串防CSRF攻击
        $state = md5(uniqid(rand(), TRUE));
        $this->_session->write('state',$state);

        //-------构造请求参数列表
        $keysArr = array(
            "response_type" => "code",
            "client_id"     => $this->_appid,
            "redirect_uri"  => $this->_callback,
            "state"         => $state,
            "scope"         => $this->_scope
        );
        $login_url = \ounun\url(self::GET_AUTH_CODE_URL,$keysArr);
        \ounun\go_url($login_url);
    }

    public function callback($args)
    {
        $state = $this->_session->read("state");

        //--------验证state防止CSRF攻击
        if($args['state'] != $state)
        {
            return array(false,'The state does not match. You may be a victim of CSRF.');
        }

        //-------请求参数列表
        $keysArr = array(
            "grant_type"    => "authorization_code",
            "client_id"     => $this->_appid,
            "redirect_uri"  => $this->_callback,
            "client_secret" => $this->_appkey,
            "code"          => $args['code']
        );

        //------构造请求access_token的url
        $token_url = \ounun\url(self::GET_ACCESS_TOKEN_URL, $keysArr);
        $response  = \ounun\Http::get_contents($token_url);
        // echo $token_url."<br />";
        // echo $response."<br />";

        if(strpos($response, "callback") !== false)
        {

            $lpos      = strpos($response, "(");
            $rpos      = strrpos($response, ")");
            $response  = substr($response, $lpos + 1, $rpos - $lpos -1);
            $msg       = json_decode($response,true);

            if(isset($msg['error']))
            {
                return array(false,"{$msg['error_description']}({$msg['error']})");
            }
        }

        $params = array();
        parse_str($response, $params);

        $this->_session->write("refresh_token", $params["refresh_token"]);
        $this->_session->write("access_token",  $params["access_token"]);
        return $params["access_token"];

    }

    public function openid()
    {

        //-------请求参数列表
        $keysArr = array(
            "access_token" => $this->_session->read("access_token")
        );

        $graph_url = \ounun\url(self::GET_OPENID_URL, $keysArr);
        $response  = \ounun\Http::get_contents($graph_url);

        //--------检测错误是否发生
        if(strpos($response, "callback") !== false)
        {
            $lpos     = strpos($response, "(");
            $rpos     = strrpos($response, ")");
            $response = substr($response, $lpos + 1, $rpos - $lpos -1);
        }

        $user = json_decode($response,true);
        if(isset($user['error']))
        {
            return array(false,"{$user['error_description']}({$user['error']})");
        }
        //------记录openid
        $this->_session->write("openid", $user['openid']);
        return $user['openid'];
    }

    /**
     * _call
     * 魔术方法，做api调用转发
     * @param string $name    调用的方法名称
     * @param array $arg      参数列表数组
     * @since 5.0
     * @return array          返加调用结果数组
     */
    public function __call($name,$arg)
    {
        // _api_map_int
        if(null == $this->_api_map)
        {
            $this->_api_map_int();
        }
        //如果APIMap不存在相应的api
        if(empty($this->_api_map[$name]))
        {
            $this->error("api调用名称错误","不存在的API: <span style='color:red;'>$name</span>");
        }

        //从APIMap获取api相应参数
        $baseUrl  = $this->_api_map[$name][0];
        $argsList = $this->_api_map[$name][1];
        $method   = isset($this->_api_map[$name][2]) ? $this->_api_map[$name][2] : "GET";

        if(empty($arg))
        {
            $arg[0] = null;
        }

        //对于get_tenpay_addr，特殊处理，php json_decode对\xA312此类字符支持不好
        if($name != "get_tenpay_addr")
        {
            $responseArr = json_decode($this->_api_apply($arg[0], $argsList, $baseUrl, $method),true);
        }else{
            $responseArr = $this->simple_json_parser($this->_api_apply($arg[0], $argsList, $baseUrl, $method));
        }


        //检查返回ret判断api是否成功调用
        if($responseArr['ret'] == 0)
        {
            return $responseArr;
        }else
        {
            $this->error($responseArr['ret'], $responseArr['msg']);
        }
    }

    //调用相应api
    private function _api_apply($arr, $argsList, $base_url, $method)
    {
        $pre           = "#";
        $keysArr       = array(
            "oauth_consumer_key" => $this->_appid,
            "access_token"       => $this->_session->read('access_token'),
            "openid"             => $this->_session->read('openid'),
        );
        $optionArgList = array();//一些多项选填参数必选一的情形
        foreach($argsList as $key => $val)
        {
            $tmpKey = $key;
            $tmpVal = $val;

            if(!is_string($key))
            {
                $tmpKey = $val;

                if(strpos($val,$pre) === 0)
                {
                    $tmpVal = $pre;
                    $tmpKey = substr($tmpKey,1);
                    if(preg_match("/-(\d$)/", $tmpKey, $res))
                    {
                        $tmpKey = str_replace($res[0], "", $tmpKey);
                        $optionArgList[$res[1]][] = $tmpKey;
                    }
                }else
                {
                    $tmpVal = null;
                }
            }

            //-----如果没有设置相应的参数
            if(!isset($arr[$tmpKey]) || $arr[$tmpKey] === "")
            {

                if($tmpVal == $pre)
                {//则使用默认的值
                    continue;
                }else if($tmpVal)
                {
                    $arr[$tmpKey] = $tmpVal;
                }else
                {
                    if($v = $_FILES[$tmpKey])
                    {
                        $filename = dirname($v['tmp_name'])."/".$v['name'];
                        move_uploaded_file($v['tmp_name'], $filename);
                        $arr[$tmpKey] = "@$filename";
                    }else
                    {
                        $this->error("api调用参数错误","未传入参数{$tmpKey}");
                    }
                }
            }
            $keysArr[$tmpKey] = $arr[$tmpKey];
        }
        //检查选填参数必填一的情形
        foreach($optionArgList as $val)
        {
            $n = 0;
            foreach($val as $v)
            {
                if(in_array($v, array_keys($keysArr)))
                {
                    $n ++;
                }
            }

            if(! $n)
            {
                $str = implode(",",$val);
                $this->error("api调用参数错误",$str."必填一个");
            }
        }

        if($method == "POST")
        {
            if($base_url == "https://graph.qq.com/blog/add_one_blog")
            {
                $response = $this->post($base_url, $keysArr, 1);
            }
            else
            {
                $response = $this->post($base_url, $keysArr, 0);
            }
        }else if($method == "GET")
        {
            $response = $this->get($base_url, $keysArr);
        }
        return $response;
    }
    /**
     *  初始化APIMap
     *     加#表示非必须，无则不传入url(url中不会出现该参数)， "key" => "val" 表示key如果没有定义则使用默认值val
     *     规则 array( baseUrl, argListArr, method)
     */
    private function _api_map_int()
    {
        $this->_api_map  = array(
            /*                       qzone                    */
            "add_blog" => array(
                "https://graph.qq.com/blog/add_one_blog",
                array("title", "format" => "json", "content" => null),
                "POST"
            ),
            "add_topic" => array(
                "https://graph.qq.com/shuoshuo/add_topic",
                array("richtype","richval","con","#lbs_nm","#lbs_x","#lbs_y","format" => "json", "#third_source"),
                "POST"
            ),
            "get_user_info" => array(
                "https://graph.qq.com/user/get_user_info",
                array("format" => "json"),
                "GET"
            ),
            "add_one_blog" => array(
                "https://graph.qq.com/blog/add_one_blog",
                array("title", "content", "format" => "json"),
                "GET"
            ),
            "add_album" => array(
                "https://graph.qq.com/photo/add_album",
                array("albumname", "#albumdesc", "#priv", "format" => "json"),
                "POST"
            ),
            "upload_pic" => array(
                "https://graph.qq.com/photo/upload_pic",
                array("picture", "#photodesc", "#title", "#albumid", "#mobile", "#x", "#y", "#needfeed", "#successnum", "#picnum", "format" => "json"),
                "POST"
            ),
            "list_album" => array(
                "https://graph.qq.com/photo/list_album",
                array("format" => "json")
            ),
            "add_share" => array(
                "https://graph.qq.com/share/add_share",
                array("title", "url", "#comment","#summary","#images","format" => "json","#type","#playurl","#nswb","site","fromurl"),
                "POST"
            ),
            "check_page_fans" => array(
                "https://graph.qq.com/user/check_page_fans",
                array("page_id" => "314416946","format" => "json")
            ),
            /*                    wblog                             */
            "add_t" => array(
                "https://graph.qq.com/t/add_t",
                array("format" => "json", "content","#clientip","#longitude","#compatibleflag"),
                "POST"
            ),
            "add_pic_t" => array(
                "https://graph.qq.com/t/add_pic_t",
                array("content", "pic", "format" => "json", "#clientip", "#longitude", "#latitude", "#syncflag", "#compatiblefalg"),
                "POST"
            ),
            "del_t" => array(
                "https://graph.qq.com/t/del_t",
                array("id", "format" => "json"),
                "POST"
            ),
            "get_repost_list" => array(
                "https://graph.qq.com/t/get_repost_list",
                array("flag", "rootid", "pageflag", "pagetime", "reqnum", "twitterid", "format" => "json")
            ),
            "get_info" => array(
                "https://graph.qq.com/user/get_info",
                array("format" => "json")
            ),
            "get_other_info" => array(
                "https://graph.qq.com/user/get_other_info",
                array("format" => "json", "#name", "fopenid")
            ),
            "get_fanslist" => array(
                "https://graph.qq.com/relation/get_fanslist",
                array("format" => "json", "reqnum", "startindex", "#mode", "#install", "#sex")
            ),
            "get_idollist" => array(
                "https://graph.qq.com/relation/get_idollist",
                array("format" => "json", "reqnum", "startindex", "#mode", "#install")
            ),
            "add_idol" => array(
                "https://graph.qq.com/relation/add_idol",
                array("format" => "json", "#name-1", "#fopenids-1"),
                "POST"
            ),
            "del_idol" => array(
                "https://graph.qq.com/relation/del_idol",
                array("format" => "json", "#name-1", "#fopenid-1"),
                "POST"
            ),
            /*                           pay                          */
            "get_tenpay_addr" => array(
                "https://graph.qq.com/cft_info/get_tenpay_addr",
                array("ver" => 1,"limit" => 5,"offset" => 0,"format" => "json")
            )
        );
    }
    /**
     * showError
     * 显示错误信息
     * @param int $code    错误代码
     * @param string $description 描述信息（可选）
     */
    public function error($code, $description = '$')
    {
        echo "<meta charset=\"UTF-8\">";
        echo "<h3>error:</h3>$code";
        echo "<h3>msg  :</h3>$description";
        exit();
    }
    /**
     * get
     * get方式请求资源
     * @param string $url     基于的baseUrl
     * @param array $keysArr  参数列表数组
     * @return string         返回的资源内容
     */
    public function get($url, $keysArr)
    {
        $combined = \ounun\url($url, $keysArr);
        return \ounun\Http::get_contents($combined);
    }

    /**
     * post
     * post方式请求资源
     * @param string $url       基于的baseUrl
     * @param array $keysArr    请求的参数列表
     * @param int $flag         标志位
     * @return string           返回的资源内容
     */
    public function post($url, $keysArr, $flag = 0)
    {
        $ch = curl_init();
        if(! $flag)
        {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        }
        curl_setopt($ch,     CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch,     CURLOPT_POST,           TRUE);
        curl_setopt($ch,     CURLOPT_POSTFIELDS,     $keysArr);
        curl_setopt($ch,     CURLOPT_URL,            $url);
        $ret = curl_exec($ch);
        curl_close($ch);
        return $ret;
    }
}
