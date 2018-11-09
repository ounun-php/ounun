<?php
namespace uitls\pics;

class comm extends \uitls\comm
{
    /**
     * @param \ounun\mysqli $db
     * @param array $data
     * @param string $table_name
     * @param int $pic_id
     * @param int $mod_id
     */
    public static function pic_install(\ounun\mysqli $db,array $data,string $table_name,int $pic_id,int $mod_id )
    {
        $data_only = "{$table_name}/{$pic_id}";
        $bind      = ['data_only'=>$data_only,'mod_id'=>$mod_id];
        $rs        = $db->row("SELECT `data_id` FROM `data` where `data_only` = :data_only and `mod_id` =:mod_id limit 1;",$bind);
        if($rs && $rs['data_id'])
        {
            return $rs['data_id'];
        }
        $tags      = json_decode($data['pic_tag'],true);
        $exts      = json_decode($data['pic_centent'],true);
        $exts      = [
            'litpic'    => $exts['data'][0],
            'imgurls'   => $exts['data'],
            'recpic'    => $exts['data'][1]
        ];
        $bind      = [
            // 'data_id' => $data['data_id'],
            'mod_id'        => $mod_id,
            'category_id'   => $data['category_id'],
            'category_sub'  => $data['category_sub']?$data['category_sub']:'0',
            'title'         => $data['pic_title'],
            'title_sub'     => $data['title_sub']  ?$data['title_sub']  :'',
            'title_color'   => $data['title_color']?$data['title_color']:'',
            'tag'           => implode(',',$tags),//json_encode($tags,JSON_UNESCAPED_UNICODE),
            'writer'        => $data['writer']?$data['writer']:'',
            'source'        => $data['source']?$data['source']:'',
            'times'         => $data['times']?$data['times']:rand ( 5000 , 100000 ),
            'scores'            => $data['scores']?$data['scores']:'0',
            'time_add'          => $data['time_add']?$data['time_add']:time(),
            'time_pub'          => $data['time_pub']?$data['time_pub']:time(),
            'centent'           => $data['centent']?$data['centent']:'',
            'seo_title'         => $data['seo_title']?$data['seo_title']:'',
            'seo_keywords'      => $data['seo_keywords']?$data['seo_keywords']:'',
            'seo_description'   => $data['seo_description']?$data['seo_description']:'',
            'data_only'         => $data_only,
            'exts'              => json_encode($exts,JSON_UNESCAPED_UNICODE),
        ];
        $data_id  = $db->insert('`data`',$bind);
        if($data_id)
        {
            self::pic_install_tag($db,$data_id,$mod_id,$tags);
        }
        return $data_id;
    }

    /**
     * @param \ounun\mysqli $db
     * @param int $data_id
     * @param int $mod_id
     * @param array $tags
     */
    public static function pic_install_tag(\ounun\mysqli $db,int $data_id,int $mod_id,array $tags)
    {
        if($tags)
        {
            foreach ($tags as $tag)
            {
                $tag_id  = self::tag_id($db,$tag);
                if($tag_id && $data_id)
                {
                    $bind_tag_idx = ['mod_id'=>$mod_id,'tag_id'=>$tag_id,'data_id'=>$data_id];
                    $db->insert('`tag_idx`',$bind_tag_idx);
                }
            }
        }
    }

    /**
     * @param $mod
     * @return [$cls,$data_id,$page]
     */
    public static function mod2cdp($mod)
    {
        // print_r($mod);
        if($mod[1])
        {
            if($mod[2])
            {
                $cls         = $mod[1];
                $data_strs   = explode('_',$mod[2]);
                if('list' == $data_strs[0])
                {
                    $data_id = 0;
                    $page    = (int)$data_strs[1];
                }else
                {
                    $data_id =      $data_strs[0];
                    $page    = (int)$data_strs[1];
                }
            }else
            {
                $data_id     = 0;
                $data_strs   = explode('_',$mod[1]);
                if('list' == $data_strs[0])
                {
                    $cls     =      '';
                    $page    = (int)$data_strs[1];
                }else
                {
                    $cls     =      $mod[1];
                    $page    = (int)$data_strs[1];
                }
            }
        }else
        {
            $cls        = '';
            $data_id    = 0;
            $page       = 0;
        }
        return [$cls,$data_id,$page];
    }

    /**
     * 图片相关
     * @param $v
     * @param string $webp
     * @return string
     */
    public static function pic_lit($v, $webp='')
    {
        $pic_url = $v['exts']['litpic'];
        if($v['data_only'])
        {
            return self::static_v2(explode('/',$v['data_only'])[0],$pic_url,$webp);
        }else
        {
            return self::static_pics($pic_url,$webp);
        }
    }

    /**
     * @param $v
     * @param string $webp
     * @return string
     */
    public static function pic_rec($v,$webp='')
    {
        $pic_url = $v['exts']['recpic'];
        if($v['data_only'])
        {
            return self::static_v2(explode('/',$v['data_only'])[0],$pic_url,$webp);
        }else
        {
            return self::static_pics($pic_url,$webp);
        }
    }

    /**
     * 大图
     * @param $v
     * @param string $webp
     * @return string
     */
    public static function pic_big($v,$webp='')
    {
        $pic_url = $v['exts']['bigpic'];
        if($v['data_only'])
        {
            return self::static_v2(explode('/',$v['data_only'])[0],$pic_url,$webp);
        }else
        {
            return self::static_pics($pic_url,$webp);
        }
    }

    /**
     * @param $v
     * @param $i
     * @param string $webp
     * @return string
     */
    public static function pic($v, $i, $webp='')
    {
        $imgurls   = $v['exts']['imgurls'];
        if($v['data_only'])
        {
            return self::static_v2(explode('/',$v['data_only'])[0],$imgurls[$i-1],$webp);
        }else
        {
            $pic_url   = 'moko8/allimg/'.$imgurls[$i-1];
            return self::static_pics( $pic_url,$webp);
        }
    }

    /**
     * @param $v
     * @param int $page
     * @param int $total
     * @param string $pre
     * @param string $next
     * @return string
     */
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

    /**
     * @param $v
     * @param int $page
     * @return string
     */
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
        return \scfg::$url_res.$url.$webp;
    }

    /**
     * @param  $centent
     * @return string
     */
    public static function centent_pics($centent)
    {
       return str_replace('/uploads/',\scfg::$url_res.'moko8/',$centent);
    }
}
