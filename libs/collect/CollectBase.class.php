<?php
/** 命名空间 */
namespace collect;
use ounun\Util;

/**
 * Created by PhpStorm.
 * User: dreamxyp
 * Date: 16/9/8
 * Time: 23:34
 */
class CollectBase
{
    /**
     * 采集内核
     * @param $url
     * @param int $timeout
     * @param $referer
     * @return bool|mixed|string
     */
    public function file_get_contents($url,$timeout=10,$referer='')
    {
        if(function_exists('curl_init'))
        {
            $ch = curl_init();
            curl_setopt ($ch, CURLOPT_URL, $url);
            curl_setopt ($ch, CURLOPT_HEADER, 0);
            curl_setopt ($ch, CURLOPT_TIMEOUT, $timeout);
            curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT,$timeout);
            if($referer)
            {
                curl_setopt ($ch, CURLOPT_REFERER, $referer);
            }
            $content = curl_exec($ch);
            curl_close($ch);
            if($content)
            {
                return $content;
            }
        }
        $ctx     = stream_context_create(['http'=>['timeout'=>$timeout]]);
        $content = file_get_contents($url, 0, $ctx);
        if($content)
        {
            return $content;
        }
        return false;
    }


    /**
     * TAG分词自动获取
     * @param $title
     * @param $content
     * @return bool|string
     */
    public  function tag_auto($title,$content)
    {
        $content = Util::msubstr($content,0,500);
        $data    = $this->file_get_contents('http://keyword.discuz.com/related_kw.html?ics=utf-8&ocs=utf-8&title='.rawurlencode($title).'&content='.rawurlencode($content));
        if($data)
        {
            $parser = xml_parser_create();
            xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
            xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE,   1);
            xml_parse_into_struct($parser, $data, $values, $index);
            xml_parser_free($parser);
            $kws = array();
            foreach($values as $valuearray)
            {
                if($valuearray['tag'] == 'kw')
                {
                    if(strlen($valuearray['value']) > 3)
                    {
                        $kws[] = trim($valuearray['value']);
                    }
                }elseif($valuearray['tag'] == 'ekw')
                {
                    $kws[] = trim($valuearray['value']);
                }
            }
            return implode(',',$kws);
        }
        return false;
    }


}