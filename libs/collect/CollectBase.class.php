<?php
/** 命名空间 */
namespace collect;

/**
 * Created by PhpStorm.
 * User: dreamxyp
 * Date: 16/9/8
 * Time: 23:34
 */
class CollectBase
{
    /**
     * URL请求
     * @param $url
     * @return string
     */
    public  function file_get_contents($url)
    {
        if(function_exists('\curl_init'))
        {
            $ch = \curl_init();
            \curl_setopt($ch, CURLOPT_URL, $url);
            \curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            \curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            \curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            \curl_setopt($ch, CURLOPT_AUTOREFERER, true);
            \curl_setopt($ch, CURLOPT_REFERER, $url);
            \curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            \curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/46.0.2490.71 Safari/537.36');
            $result = \curl_exec($ch);
            \curl_close($ch);
        }elseif(\version_compare(PHP_VERSION, '5.0.0')>=0)
        {
            $opts = array(
                'http' => array(
                    'header' => "Referer:{$url}"
                )
            );
            $result = \file_get_contents($url,false,\stream_context_create($opts));
        }else
        {
            $result = \file_get_contents($url);
        }
        return $result;
    }

    public function slice_left($left,$right,$content)
    {
        return explode($right, explode($left,$content,2)[1],2)[0];
    }

    public function slice_left_multi($left_multi,$right,$content)
    {
        foreach ($left_multi as $left)
        {
            $content = explode($left,$content,2)[1];
        }
        return explode($right, $content,2)[0];
    }

    public function slice_right($left,$right,$content)
    {


    }


    /**
     * TAG分词自动获取
     * @param $title
     * @param $content
     * @return bool|string
     */
    public  function tag_auto($title,$content)
    {
        $content = \ounun\Util::msubstr($content,0,500);
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