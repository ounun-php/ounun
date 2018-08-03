<?php

namespace uitls_pics;

class comm
{
    /**
     * @param $mod
     * @return [$cls,$data_id,$page]
     */
    public static function mod2cdp($mod)
    {
        // print_r($mod);
        if($mod[1]){
            if($mod[2]){
                $cls         = $mod[1];
                $data_strs   = explode('_',$mod[2]);
                if('list' == $data_strs[0]){
                    $data_id = 0;
                    $page    = (int)$data_strs[1];
                }else{
                    $data_id =      $data_strs[0];
                    $page    = (int)$data_strs[1];
                }
            }else{
                $data_id     = 0;
                $data_strs   = explode('_',$mod[1]);
                if('list' == $data_strs[0]){
                    $cls     =      '';
                    $page    = (int)$data_strs[1];
                }else{
                    $cls     =      $mod[1];
                    $page    = (int)$data_strs[1];
                }
            }
        }else{
            $cls        = '';
            $data_id    = 0;
            $page       = 0;
        }
        return [$cls,$data_id,$page];
    }

    /** 图片相关  */
    // http://i.383434.com/6mm/7328/2.jpg?imageView2/1/w/661/h/371
    // http://i.383434.com/6mm/7328/2.jpg?imageView2/1/w/318/h/439

    public static function pic_lit($v, $webp='')
    {
        $pic_url = $v['exts']['litpic'];
        return self::static_pics($pic_url,$webp);
    }

    public static function pic_rec($v,$webp='')
    {
        $pic_url = $v['exts']['recpic'];
        return self::static_pics($pic_url,$webp);
    }

    public static function pic_big($v,$webp='')
    {
        $pic_url = $v['exts']['bigpic'];
        return self::static_pics($pic_url,$webp);
    }



    public static function pic($v, $i, $webp='')
    {
        $imgurls   = $v['exts']['imgurls'];
        $pic_url   = 'moko8/allimg/'.$imgurls[$i-1];
        return self::static_pics( $pic_url,$webp);
    }



    public static function url_pics_details($v,$page=1,$total=1,$pre='',$next='')
    {
        $cls = \site_cfg::maps[$v['category_id']];
        if($page == 1)
        {
            return "/p/{$cls}/{$v['data_id']}.html";
        }elseif($page < 1)
        {
            return $pre;
        }else
        {
            if($page > $total)
            {
                return $next;
            }else
            {
                return "/p/{$cls}/{$v['data_id']}_{$page}.html";
            }
        }
    }

    public static function url_news_details($v,$page=1)
    {
        $cls = \site_cfg::maps[$v['category_id']];
        if($page == 1)
        {
            return "/news/{$cls}/{$v['data_id']}.html";
        }else{
            return "/news/{$cls}/{$v['data_id']}_{$page}.html";
        }
    }

    /**
     * 静态 图片 地址
     * @param string $msg  URL
     */
    public static function static_pics($url,$webp)
    {
        $url = str_replace('/uploads/','moko8/',$url);
        return Const_Url_Pics.$url.$webp;
    }


    public static function centent_pics($centent)
    {
       return str_replace('/uploads/',Const_Url_Pics.'moko8/',$centent);
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
                    'http' => [ 'method'            => "GET", 'header'       => "Referer: {$referer}" ],
                    "ssl"  => [ "allow_self_signed" => true , "verify_peer"  => false, ],
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
