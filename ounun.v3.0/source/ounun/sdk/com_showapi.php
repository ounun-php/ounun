<?php

namespace ounun\sdk;

class com_showapi
{
    /**
     * @param $text
     * @return array
     */
    public static function tag($text)
    {
        sleep(1);
        $showapi_appid = '67516';                             // 替换此值,在官网的"我的应用"中找到相关值
        $showapi_secret = 'e7a782c3ec6d450489a6716453b4b2b5';  // 替换此值,在官网的"我的应用"中找到相关值
        $paramArr = [
            'showapi_appid' => $showapi_appid,
            'text' => $text,
            'num' => "10"       // 添加其他参数
        ];

        $url = 'http://route.showapi.com/941-1?' . self::_params_create($paramArr, $showapi_secret);
        $c = file_get_contents($url);
        $c2 = json_decode($c, true);
        if ($c2 && $c2['showapi_res_body'] && $c2['showapi_res_body']['list']) {
            return $c2['showapi_res_body']['list'];
        } else {
            $url2 = 'http://zhannei.baidu.com/api/customsearch/keywords?title=' . urlencode($text);
            $c2 = file_get_contents($url2);
            $c3 = json_decode($c2, true);
            if ($c3 && $c3['result'] && $c3['result']['res'] && $c3['result']['res']['keyword_list']) {
                return $c3['result']['res']['keyword_list'];
            }
//          echo "\$url:{$url}\n";
//          echo "\$c:{$c}\n";
        }
        return [];
    }

    /**
     * @param $text
     * @return mixed
     */
//    protected static function tag_baidu($text)
//    {
//        $url2 = 'http://zhannei.baidu.com/api/customsearch/keywords?title='.urlencode($text);
//        $c2   = file_get_contents($url2);
//        $c3   = json_decode($c2,true);
//        if($c3 && $c3['result'] && $c3['result']['res'] && $c3['result']['res']['keyword_list'])
//        {
//            return $c3['result']['res']['keyword_list'];
//        }
//    }


    /**
     * @param $paras
     * @param $showapi_secret
     * @return string
     */
    protected static function _params_create($paras, $showapi_secret)
    {
        $paraStr = "";
        $signStr = "";
        ksort($paras);
        foreach ($paras as $key => $val) {
            if ($key != '' && $val != '') {
                $signStr .= $key . $val;
                $paraStr .= $key . '=' . urlencode($val) . '&';
            }
        }
        $signStr .= $showapi_secret;           // 排好序的参数加上secret,进行md5
        $sign = strtolower(md5($signStr));
        $paraStr .= 'showapi_sign=' . $sign;     // 将md5后的值作为参数,便于服务器的效验
        // echo "排好序的参数:".$signStr."\r\n";
        return $paraStr;
    }
}
