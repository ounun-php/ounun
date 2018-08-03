<?php
namespace collect;

class db
{
    /** @var \ounun\mysqli  */
    static public $db = null;


    static public function pic_data_99mm(\ounun\mysqli $db,$datas)
    {
        self::$db    = $db;
        $ids         = [];
        foreach ($datas as $data)
        {
            $site_name   = "99mm.me/{$data['pic_id']}";
            $rs          = self::$db->row('SELECT `pic_id` FROM `pic_data` where `site_name` = :site_name limit 1;',['site_name'=>$site_name]);
            // echo self::$db->sql()."\n";
            if(!$rs)
            {
                $pic_tag   = json_decode($data['pic_tag'],true);
                $pic_class = self::pic_data_99mm_collclass2picclass($data['pic_origin_url']);
                $pic_tag   = implode(',',$pic_tag);
                $pic_ext   = $data['pic_centent'];
                // $pic_exts  = array_merge([$pic_ext['cover']],$pic_ext['data']);
                $pic_exts  = $pic_ext['data'];
                foreach ($pic_exts as &$v)
                {
                    $v = '99mm/'.$v;
                }
                $is_gaoqing = rand ( 0 , 10000 ) < 1000?1:0;
                $is_hot     = rand ( 0 , 20000 ) < 1000?1:0;
                $is_goods   = rand ( 0 , 10000 ) < 1000?1:0;
                $bind    = [
                    'pic_class'   => $pic_class,
                    'pic_title'   => $data['pic_title'],
                    'pic_tag'     => $pic_tag,
                    'pic_centent' => \mm_pics::pics_class[$pic_class].", ".$data['pic_title'].", ".$pic_tag,
                    'pic_ext'     => $pic_exts,
                    'pic_cover'   => 0,
                    'pic_thum'    => 0,
                    'is_gaoqing'  => $is_gaoqing,
                    'is_hot'      => $is_hot,
                    'is_goods'    => $is_goods,
                    'is_done'     => 1,
                    'site_name'   => $site_name
                ];
//                print_r($data);
//                print_r($bind);
                $pic_id      = self::pic_data($bind);
                // $pic_id      = 0;
                if($pic_id){
                    $ids[]   = $data['pic_id'];
                }
            }else
            {
                $ids[]       = $data['pic_id'];
            }
        }
        return $ids;
    }


    static public function pic_data_mm131(\ounun\mysqli $db,$datas)
    {
        self::$db    = $db;
        $ids         = [];
        foreach ($datas as $data)
        {
            $site_name   = "mm131.com/{$data['pic_id']}";
            $rs          = self::$db->row('SELECT `pic_id` FROM `pic_data` where `site_name` = :site_name limit 1;',['site_name'=>$site_name]);
            // echo self::$db->sql()."\n";
            if(!$rs)
            {
                $pic_tag   = json_decode($data['pic_tag'],true);
                $pic_class = self::pic_data_mm131_collclass2picclass($data['pic_origin_url']);
                $pic_tag   = implode(',',$pic_tag);
                $pic_ext   = $data['pic_centent'];
                // $pic_exts  = array_merge([$pic_ext['cover']],$pic_ext['data']);
                $pic_exts  = $pic_ext['data'];
                foreach ($pic_exts as &$v)
                {
                    $v = '131/'.$v;
                }
                $is_gaoqing = 0;
                $is_hot     = 0;
                $is_goods   = 0;
                $bind    = [
                    'pic_class'   => self::pic_data_mm131_collclass2picclass($data['pic_origin_url']),
                    'pic_title'   => $data['pic_title'],
                    'pic_tag'     => $pic_tag,
                    'pic_centent' => \mm_pics::pics_class[$pic_class].", ".$data['pic_title'].", ".$pic_tag,
                    'pic_ext'     => $pic_exts,
                    'pic_cover'   => 0,
                    'pic_thum'    => 0,
                    'is_gaoqing'  => $is_gaoqing,
                    'is_hot'      => $is_hot,
                    'is_goods'    => $is_goods,
                    'is_done'     => 1,
                    'site_name'   => $site_name
                ];
                //                print_r($data);
                //                print_r($bind);
                $pic_id      = self::pic_data($bind);
                // $pic_id      = 0;
                if($pic_id){
                    $ids[]   = $data['pic_id'];
                }
            }else
            {
                $ids[]       = $data['pic_id'];
            }
        }
        return $ids;
    }

    static public function pic_data($data)
    {
        $bind = [
            // 'pic_id'       => $data['pic_id'],
            'pic_class'    => $data['pic_class'],
            'pic_title'    => $data['pic_title'],
            'pic_tag'      => $data['pic_tag'],
            'pic_centent'  => $data['pic_centent'],
            'pic_ext'      => serialize($data['pic_ext']),
            'pic_cover'    => $data['pic_cover'],
            'pic_thum'     => $data['pic_thum'],
            'pic_times'    => $data['pic_times']?$data['pic_times']:rand ( 5000 , 100000 ),
            'is_gaoqing'   => $data['is_gaoqing'],
            'is_hot'       => $data['is_hot'],
            'is_goods'     => $data['is_goods'],
            'is_done'      => $data['is_done'],
            'site_name'    => $data['site_name'],
            'add_time'     => $data['add_time']?$data['add_time']:time(),
        ];
        //$pic_id = $db->insert('`pic_data`',$bind);
        $pic_id   = self::pic_data_save($bind);
        echo "news ------> $pic_id:{$pic_id} ok!!!\n";
        return $pic_id;
    }


    static private function pic_data_save($data)
    {
        $pic_id  = self::$db->insert('`pic_data`',$data);
        //
        $tag     = $data['pic_tag'];
        $tag_ids = [];
        if($tag)
        {
            $tag = explode(',',$tag);
            foreach($tag as $v)
            {
                $rs = self::$db->row('SELECT `tag_id` FROM  `pic_tag` where `tag` = ? LIMIT 0 , 1;',$v);
                if($rs && $rs['tag_id'])
                {
                    $tag_bind           = ['tag'=>$v,'tag_id'=> $rs['tag_id']];
                }else
                {
                    $tag_bind           = ['tag'=>$v];
                    $tag_bind['tag_id'] = self::$db->insert('`pic_tag`',$tag_bind);
                }
                $tag_ids[]              = $tag_bind;
            }
        }
        //echo "\$tag_ids\n";
        //print_r($tag_ids);echo "\n";
        $tag_datas = [];
        if($tag_ids && is_array($tag_ids))
        {
            foreach($tag_ids as $v)
            {
                $tag_datas[] = ['pic_id'=>$pic_id,'tag_id'=>$v['tag_id']];
            }
        }
        //echo "\$tag_datas\n";
        //print_r($tag_datas);echo "\n";
        if($tag_datas)
        {
            self::$db->insert('`pic_tag_data`',$tag_datas);
        }
        return $pic_id;
    }



    static public function pic_data_mm131_collclass2picclass($pic_origin_url)
    {
        $coll = explode('/',$pic_origin_url)[3];

//        $list_url = [
//            'mingxing'  => 'http://www.mm131.com/mingxing/list_5_{page}.html',
//            'xinggan'   => 'http://www.mm131.com/xinggan/list_6_{page}.html',
//            'qingchun'  => 'http://www.mm131.com/qingchun/list_1_{page}.html',
//            'xiaohua'   => 'http://www.mm131.com/xiaohua/list_2_{page}.html',
//            'chemo'     => 'http://www.mm131.com/chemo/list_3_{page}.html',
//            'qipao'    => 'http://www.mm131.com/qipao/list_4_{page}.html',
//        ];

        $rs =[
            'meitui'   => 'meitui',
            'qingchun' => 'qingchun',
            'xinggan'  => 'xinggan',
            'xiaohua'  => 'wangluo',

            'chemo'    => 'jiepai',
            'mingxing' => 'mingxing',
            'qipao'    => 'wangluo',
        ];
        return $rs[$coll]?$rs[$coll]:'wangluo';
    }


    static public function pic_data_99mm_collclass2picclass($pic_origin_url)
    {
        $coll = explode('/',$pic_origin_url)[1];

//      $list_url = [
//          'meitui'   => 'http://www.99mm.me/meitui/mm_1_{page}.html',
//          'xinggan'  => 'http://www.99mm.me/xinggan/mm_2_{page}.html',
//          'qingchun' => 'http://www.99mm.me/qingchun/mm_3_{page}.html',
//          'hot'      => 'http://www.99mm.me/hot/mm_4_{page}.html',
//      ];

        $rs =[
            'meitui'   => 'meitui',
            'qingchun' => 'qingchun',
            'xinggan'  => 'xinggan',
            'hot'      => 'wangluo',

            'jiepai'   => 'jiepai',
            'mingxing' => 'mingxing',
            'wangluo'  => 'wangluo',
        ];
        return $rs[$coll]?$rs[$coll]:'wangluo';
    }

    /** 刷新tag */
    static public function pic_tag_count()
    {
        $rs = self::$db->data_array("SELECT  `tag_id` , COUNT( * ) AS  `tag_count` FROM  `pic_tag_data` GROUP BY  `tag_id` ");
        if($rs)
        {
            foreach($rs as $v)
            {
                self::$db->update('`pic_tag`',['tag_count'=>$v['tag_count']],' `tag_id` = ? ',$v['tag_id']);
            }
        }
    }

}
