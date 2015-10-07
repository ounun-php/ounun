<?php
/**
 * Created by PhpStorm.
 * User: dreamxyp
 * Date: 14-9-20
 * Time: 下午3:58
 */
namespace sdk\oauth\qq;

class QQOAuth2
{
    /**
     * @ignore
     */
    public $client_id;
    /**
     * @ignore
     */
    public $client_secret;
    /**
     * @ignore
     */
    public $access_token;
    /**
     * @ignore
     */
    public $refresh_token;
    /**
     * construct OAuth object
     */
    public function __construct($client_id, $client_secret, $access_token = NULL, $refresh_token = NULL)
    {
        $this->client_id     = $client_id;
        $this->client_secret = $client_secret;
        $this->access_token  = $access_token;
        $this->refresh_token = $refresh_token;
    }


    function qq_callback()
    {
        //debug
        print_r($_REQUEST);
        echo "\n<br />\n";
        print_r($_SESSION);

        if($_REQUEST['state'] == $_SESSION['state']) //csrf
        {
            $token_url = "https://graph.qq.com/oauth2.0/token?grant_type=authorization_code&"
                . "client_id=" . $_SESSION["appid"]. "&redirect_uri=" . urlencode($_SESSION["callback"])
                . "&client_secret=" . $_SESSION["appkey"]. "&code=" . $_REQUEST["code"];

            $response = file_get_contents($token_url);
            if (strpos($response, "callback") !== false)
            {
                $lpos = strpos($response, "(");
                $rpos = strrpos($response, ")");
                $response  = substr($response, $lpos + 1, $rpos - $lpos -1);
                $msg = json_decode($response);
                if (isset($msg->error))
                {
                    echo "<h3>error:</h3>" . $msg->error;
                    echo "<h3>msg  :</h3>" . $msg->error_description;
                    exit;
                }
            }

            $params = array();
            parse_str($response, $params);

            //debug
            //print_r($params);

            //set access token to session
            $_SESSION["access_token"] = $params["access_token"];

        }
        else
        {
            echo("The state does not match. You may be a victim of CSRF.4");
        }
    }

    function get_openid()
    {
        $graph_url = "https://graph.qq.com/oauth2.0/me?access_token="
            . $_SESSION['access_token'];

        $str  = file_get_contents($graph_url);
        if (strpos($str, "callback") !== false)
        {
            $lpos = strpos($str, "(");
            $rpos = strrpos($str, ")");
            $str  = substr($str, $lpos + 1, $rpos - $lpos -1);
        }

        $user = json_decode($str);
        if (isset($user->error))
        {
            echo "<h3>error:</h3>" . $user->error;
            echo "<h3>msg  :</h3>" . $user->error_description;
            exit;
        }

        //debug
        //echo("Hello " . $user->openid);

        //set openid to session
        $_SESSION["openid"] = $user->openid;
    }

    public function get_user_info()
    {
        $get_user_info = "https://graph.qq.com/user/get_user_info?"
            . "access_token=" . $_SESSION['access_token']
            . "&oauth_consumer_key=" . $_SESSION["appid"]
            . "&openid=" . $_SESSION["openid"]
            . "&format=json";

        $info = file_get_contents($get_user_info);
        $arr = json_decode($info, true);

        return $arr;
    }

    public function upload_pic()
    {
        //上传照片的接口地址, 不要更改!!
        $url  = "https://graph.qq.com/photo/upload_pic";

        $params["access_token"] = $_SESSION["access_token"];
        $params["oauth_consumer_key"] = $_SESSION["appid"];
        $params["openid"] = $_SESSION["openid"];
        $params["photodesc"] = urlencode($_POST["photodesc"]);
        $params["title"] = urlencode($_POST["title"]);
        $params["albumid"] = urlencode($_POST["albumid"]);
        $params["x"] = $_POST["x"];
        $params["y"] = $_POST["y"];
        $params["format"] = $_POST["format"];

        //处理上传图片
        foreach ($_FILES as $filename => $filevalue)
        {
            $tmpfile = dirname($filevalue["tmp_name"])."/".$filevalue["name"];
            move_uploaded_file($filevalue["tmp_name"], $tmpfile);
            $params[$filename] = "@$tmpfile";
        }

        $ret =  do_post($url, $params);
        unlink($tmpfile);
        //echo $tmpfile;
        return $ret;

    }

    public function list_album()
    {
        //获取相册列表的接口地址, 不要更改!!
        $url = "https://graph.qq.com/photo/list_album?"
            ."access_token=".$_SESSION["access_token"]
            ."&oauth_consumer_key=".$_SESSION["appid"]
            ."&openid=".$_SESSION["openid"]
            ."&format=json";
        //echo $url;
        $ret = file_get_contents($url);
        return $ret;
    }

    function add_album()
    {
        //创建QQ空间相册的接口地址, 不要更改!!
        $url  = "https://graph.qq.com/photo/add_album";
        $data = "access_token=".$_SESSION["access_token"]
            ."&oauth_consumer_key=".$_SESSION["appid"]
            ."&openid=".$_SESSION["openid"]
            ."&format=".$_POST["format"]
            ."&albumname=".urlencode($_POST["albumname"])
            ."&albumdesc=".urlencode($_POST["albumdesc"])
            ."&priv=".$_POST["priv"];

        //echo $data;

        $ret =  $this->do_post($url, $data);
        return $ret;
    }

    public function add_share()
    {
        //发布一条动态的接口地址, 不要更改!!
        $url = "https://graph.qq.com/share/add_share?"
                    ."access_token=".$_SESSION["access_token"]
                    ."&oauth_consumer_key=".$_SESSION["appid"]
                    ."&openid=".$_SESSION["openid"]
                    ."&format=json"
                    ."&title=".urlencode($_REQUEST["title"])
                    ."&url=".urlencode($_REQUEST["url"])
                    ."&comment=".urlencode($_REQUEST["comment"])
                    ."&summary=".urlencode($_REQUEST["summary"])
                    ."&images=".urlencode($_REQUEST["images"]);

        //echo $url;

        $ret = file_get_contents($url);
        return $ret;
    }

    public function add_topic()
    {
        //发表QQ空间日志的接口地址, 不要更改!!
        $url  = "https://graph.qq.com/shuoshuo/add_topic";
        $data = "access_token=".$_SESSION["access_token"]
            ."&oauth_consumer_key=".$_SESSION["appid"]
            ."&openid=".$_SESSION["openid"]
            ."&format=".$_POST["format"]
            ."&richtype=".$_POST["richtype"]
            ."&richval=".urlencode($_POST["richval"])
            ."&con=".urlencode($_POST["con"])
            ."&lbs_nm=".$_POST["lbs_nm"]
            ."&lbs_x=".$_POST["lbs_x"]
            ."&lbs_y=".$_POST["lbs_y"]
            ."&third_source=".$_POST["third_source"];

        //echo $data;
        $ret = $this->do_post($url, $data);
        return $ret;
    }

    public function add_weibo()
    {
        //发表微博的接口地址, 不要更改!!
        $url  = "https://graph.qq.com/wb/add_weibo";
        $data = "access_token=".$_SESSION["access_token"]
            ."&oauth_consumer_key=".$_SESSION["appid"]
            ."&openid=".$_SESSION["openid"]
            ."&format=".$_POST["format"]
            ."&type=".$_POST["type"]
            ."&content=".urlencode($_POST["content"])
            ."&img=".urlencode($_POST["img"]);

        //echo $data;
        $ret = $this->do_post($url, $data);
        return $ret;
    }

    function add_blog()
    {
        //发表QQ空间日志的接口地址, 不要更改!!
        $url  = "https://graph.qq.com/blog/add_one_blog";
        $data = "access_token=".$_SESSION["access_token"]
            ."&oauth_consumer_key=".$_SESSION["appid"]
            ."&openid=".$_SESSION["openid"]
            ."&format=".$_POST["format"]
            ."&title=".$_POST["title"]
            ."&content=".$_POST["content"];

        $ret = $this->do_post($url, $data);
        return $ret;
    }



    private function do_post($url, $data)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_URL, $url);
        $ret = curl_exec($ch);

        curl_close($ch);
        return $ret;
    }

} 