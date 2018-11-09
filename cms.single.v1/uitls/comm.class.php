<?php
namespace uitls;

class comm
{

    protected static $_tags  = [];
    /**
     * @param string $tag
     */
    public static function tag_id(\ounun\mysqli $db,string $tag)
    {
        if(!$tag)
        {
            return 0;
        }
        if(self::$_tags[$tag])
        {
            return self::$_tags[$tag];
        }
        $rs   = $db->row("SELECT `tag_id` FROM `tag` where `tag` = ? limit 0,1;",$tag);
        if($rs && $rs['tag_id'])
        {
            self::$_tags[$tag] = $rs['tag_id'];
            return $rs['tag_id'];
        }else
        {
            $bind = [
                // 'tag_id'   => 0,
                'pinyin'   => '',
                'tag'      => $tag,
                'is_ok'    => 0,
                'official' => 0,
                'type'     => '',
                'exts'     => ''
            ];
            $tag_id = $db->insert('`tag`',$bind);
            if($tag_id)
            {
                self::$_tags[$tag] = $tag_id;
                return $tag_id;
            }
        }
        return 0;
    }

    /**
     * @param \ounun\mysqli $db
     * @param array $data
     * @param string $table_name
     * @param int $pic_id
     * @param int $mod_id
     */
    public static function data_install(\ounun\mysqli $db, array $data, string $table_name, int $data_id, int $mod_id )
    {
        $data_only = "{$table_name}/{$data_id}";
        $bind      = ['data_only'=>$data_only,'mod_id'=>$mod_id];
        $rs        = $db->row("SELECT `data_id` FROM `data` where `data_only` = :data_only and `mod_id` =:mod_id limit 1;",$bind);
        if($rs && $rs['data_id'])
        {
            return $rs['data_id'];
        }
        $tags      = json_decode($data['tag'],true);
        $data_o    = json_decode($data['dataorigin'],true);
        $exts      = [];
//        $exts      = [
//            'litpic'    => $exts['data'][0],
//            'imgurls'   => $exts['data'],
//            'recpic'    => $exts['data'][1]
//        ];
        $bind      = [
            // 'data_id' => $data['data_id'],
            'mod_id'        => $mod_id,
            'category_id'   => $data['category_id'],
            'category_sub'  => $data['category_sub']?$data['category_sub']:'0',
            'title'         => $data['title'],
            'title_sub'     => $data['title_sub']  ?$data['title_sub']  :'',
            'title_color'   => $data['title_color']?$data['title_color']:'',
            'tag'           => json_encode($tags,JSON_UNESCAPED_UNICODE),//json_encode($tags,JSON_UNESCAPED_UNICODE),
            'writer'        => $data['writer']?$data['writer']:'',
            'source'        => $data['source']?$data['source']:'',
            'times'         => $data['times'] ?$data['times'] :rand ( 5000 , 100000 ),
            'scores'            => $data['scores']  ?$data['scores']  :'0',
            'time_add'          => $data['time_add']?$data['time_add']:time(),
            'time_pub'          => $data['time_pub']?$data['time_pub']:time(),
            'centent'           => json_encode($data_o,JSON_UNESCAPED_UNICODE),
            'seo_title'         => $data['seo_title']      ?$data['seo_title']:'',
            'seo_keywords'      => $data['seo_keywords']   ?$data['seo_keywords']:'',
            'seo_description'   => $data['seo_description']?$data['seo_description']:'',
            'data_only'         => $data_only,
            'exts'              => json_encode($exts,JSON_UNESCAPED_UNICODE),
        ];
        $data_id2  = $db->insert('`data`',$bind);
        // echo $db->sql()."\n";
        if($data_id2)
        {
            self::data_install_tag($db,$data_id2,$mod_id,$tags);
        }
        return $data_id2;
    }

    /**
     * @param \ounun\mysqli $db
     * @param int $data_id
     * @param int $mod_id
     * @param array $tags
     */
    public static function data_install_tag(\ounun\mysqli $db,int $data_id,int $mod_id,array $tags)
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
                    // echo $db->sql()."\n";
                }
            }
        }
    }


    /**
     * @param $table
     * @param $url
     * @param $webp
     * @return string
     */
    public static function static_v2($table,$url,$webp)
    {
        return self::pics($table).$url.$webp;
    }


    /**
     * @param $table
     * @return string
     */
    public static function pics($table)
    {
        $root = \cfg\pics\res::table2root[$table];
        $root = $root?$root:\cfg\pics\res::default_root;
        $dir  = \cfg\pics\res::table2dir[$table];
        $dir  = $dir ?$dir :\cfg\pics\res::default_dir;
        return "{$root}{$dir}/";
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


    /**
     * @param string $str        数据
     * @param string $fields     字段多个,分格
     * @param string $delimiter  分格符
     */
    public static function str2array(string $str,string $fields,string $delimiter = "\n",$delimiter_data = ":")
    {
        $data        = explode($delimiter,$str);
        $fields2     = explode(',',$fields);
        $fields2_len = count($fields2);

        $data2  = [];
        foreach ($data as $v)
        {
            $v = trim($v);
            if($v)
            {
                $v_data = explode($delimiter_data,$v);
                $v_len  = count($v_data);
                if($fields2_len == $v_len)
                {
                    $v_data2 = [];
                    foreach ($v_data as $k2=>$v2)
                    {
                        $v_data2[$fields2[$k2]] = $v2;
                    }
                    $data2[] = $v_data2;
                }
            }
        }
        return $data2;
    }

}
