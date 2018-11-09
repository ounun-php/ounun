<?php
namespace cfg\pics;

class mm_pics
{
    const pics_class = [
        'jiepai'  => '街拍美女',
        'wangluo' => '网络美女',
        'qingchun'=> '清纯美女',
        'meitui'  => '美腿模特',
        'mingxing'=> '明星写真',
        'xinggan' => '性感美女',
    ];

    const pics_page_cfg = [
        'default' => ['' , ''] ,
        'now'     => ['' , '', ' class="cur" '] ,
        'tag'     => ['第一页' , '上一页' , '下一页' , '最后一页'],
        'index'   => ['/index_{total_page}.html','/']
    ];

    public static $url_static = '';

    public static $url_pics   = '';

    public static $url_face   = '';

    /** 图片相关  */
    // http://i.383434.com/6mm/7328/2.jpg?imageView2/1/w/661/h/371
    // http://i.383434.com/6mm/7328/2.jpg?imageView2/1/w/318/h/439

    public static function url_pic_index($v,$webp='')
    {
        $pic_ext   = unserialize($v['pic_ext']);
        $pic_cover = $v['pic_cover']?$v['pic_cover']:4;
        $pic_url   = $pic_ext[$pic_cover];
        $pic_url   = self::url_pic_inside($pic_url);
        // return self::static_pics($pic_url.'!w660h370'.$webp);
        return self::static_pics($pic_url.'!6'.$webp);
    }

    public static function url_pic_small($v,$webp='',$mode='1')
    {
        $pic_ext   = unserialize($v['pic_ext']);
        $pic_cover = $v['pic_thum'];
        $pic_url   = $pic_ext[$pic_cover];
        $pic_url   = self::url_pic_inside($pic_url);
        // return self::static_pics($pic_url.'!w318'.$webp);
        return self::static_pics($pic_url.'!'.$mode.$webp);
    }

    public static function url_pic($v,$i,$webp='')
    {
        $pic_ext   = unserialize($v['pic_ext']);
        $pic_url   = $pic_ext[$i-1];
        $pic_url   = self::url_pic_inside($pic_url);
        // return self::static_pics( $pic_url.'!w850'.$webp);
        return self::static_pics( $pic_url.'!8'.$webp);
    }

    public static  function url_pic_inside($pic_url)
    {
//        if('6mm/' == substr($pic_url,0,4))
//        {
//            return substr($pic_url,4);
//        }
        return $pic_url;
    }

    public static function url_details($v,$page=1)
    {
        if($page == 1)
        {
            return "/{$v['pic_class']}/{$v['pic_id']}.html";
        }elseif($page < 1)
        {
            return "javascript:dPlayPre();";
        }else
        {
            $pic_ext   = unserialize($v['pic_ext']);
            if($page > count($pic_ext))
            {
                return "javascript:dPlayNext();";
            }else
            {
                return "/{$v['pic_class']}/{$v['pic_id']}_{$page}.html";
            }
        }
    }

    /** 静态地址 多个 */
    public static function static_urls($url,$pre="")
    {
        if($url && is_array($url) )
        {
            if(count($url) > 1)
            {
                $url = '??'.implode(',',$url);
            }else
            {
                $url = $url[0];
            }
        }
        return self::static_url($pre.$url);
    }

    /**
     * 静态地址
     * @param string $msg  URL
     */
    public static function static_url($url)
    {
        return Const_Url_Static.$url;
    }

    /**
     * 静态 图片 地址
     * @param string $msg  URL
     */
    public static function static_pics($url)
    {
        return Const_Url_Res.$url;
    }

    /**
     * 头像地址
     * @param string $msg  URL
     */
    public static function static_url_face($uid)
    {
        return Const_Url_Res.((int)$uid).'.jpg';
    }


    /**
     * @param $text
     * @return array
     */
    public static function tag($text)
    {
        usleep(350000);
        $url = 'http://route.showapi.com/941-1?showapi_appid=67516&text='.urlencode($text).'&num=10&showapi_sign=e7a782c3ec6d450489a6716453b4b2b5';
        $c   = file_get_contents($url);
        $c2  = json_decode($c,true);
        if($c2 && $c2['showapi_res_body'] && $c2['showapi_res_body']['list'])
        {
            return $c2['showapi_res_body']['list'];
        }
        return [];
    }

    /**
     * @param $url
     * @param $referer
     * @return bool|string
     */
    public static function file_get_contents($url,$referer)
    {
        $opts = [
            'http'=> [ 'method'=>"GET", 'header'=>"Referer: {$referer}" ],
            "ssl" => [ "allow_self_signed"=>true, "verify_peer" =>false, ],
        ];
        $context = stream_context_create($opts);
        return file_get_contents($url, false, $context);
    }

    /**
     * @param array $array
     * @param int $count
     * @return array
     */
    public static function slice(array $array,int $count)
    {
        return [array_slice($array,0,$count),array_slice($array,$count)];
    }
}
