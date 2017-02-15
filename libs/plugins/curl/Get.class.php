<?php
/**
 * Created by PhpStorm.
 * User: dreamxyp
 * Date: 2016/12/6
 * Time: 19:18
 */

namespace plugins\curl;


class Get
{
    /**
     * Get constructor.
     */
    public function __construct()
    {
        // $this->isConstructCalled = true;
    }

    /**
     * URL请求
     * @param $url
     * @return string
     */
    public function request($url)
    {
        if(function_exists('curl_init'))
        {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL,            $url);
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
            $opts   = ['http' => ['header' => "Referer:{$url}"]];
            $result = file_get_contents($url,false,stream_context_create($opts));
        }else
        {
            $result = file_get_contents($url);
        }
        return $result;
    }
}