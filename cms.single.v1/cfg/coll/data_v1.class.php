<?php

namespace cfg\coll;

class data_v1
{
    /** @var array */
    protected static $_table_maps_v1 = [];
    /** @var array */
    static protected function _info_maps():array
    {
        if(self::$_table_maps_v1)
        {
            return self::$_table_maps_v1;
        }
        self::$_table_maps_v1 = [
            // ---------------------------------------------- v1
            'null'          => self::_info('', '', '', '','',''),
            // ---------------------------------------------- v1
            'pics_6mm'      => self::_info('import_data', '6mm',   '6mm.cc',                             '',                  '','http://hzyw.383434.com/'),
            'pics_99mm'     => self::_info('import_data','99mm',  '99mm.me', 'data_99mm_collclass2picclass', 'libs_v1_data_99mm','http://hzyw.383434.com/'),
            'pics_jpg'      => self::_info('import_data', 'jpg','mmjpg.com',                             '',                  '','http://hzyw.383434.com/'),
            'pics_mm131'    => self::_info('import_data', '131','mm131.com','data_mm131_collclass2picclass','libs_v1_data_mm131','http://hzyw.383434.com/'),
            'pics_xgyw'     => self::_info('import_data',  'yw',  'xgyw.cc',                             '',                  '','http://hzyw.383434.com/'),
            // ---------------------------------------------- v2
            'mp3_md'        => self::_info('outs_mp3','mp3','99mm.me','','','http://hzyw.383434.com/'),
            // ---------------------------------------------- v2
            'dtxt_75'       => self::_info('outs_txt', '75',  '75.pm','v2_dtxt_75_to_75','','http://hzyw.383434.com/','20180925',2000),
        ];
        return self::$_table_maps_v1;
    }

    /**
     * @param  string $out_table
     * @return array
     */
    static public function info(string $out_table,string $site_tag = '')
    {
        if($out_table && $site_tag)
        {
            $tag  = "{$out_table}_{$site_tag}";
            $info = self::_info_maps()[$tag];
            if($info)
            {
                return $info;
            }
            $info = self::$_table_maps_v1[$out_table];
            if($info)
            {
                return $info;
            }
        }elseif ($out_table)
        {
            $info = self::_info_maps()[$out_table];
            if($info)
            {
                return $info;
            }
        }
        return self::$_table_maps_v1['null'];
    }


    /**
     * @param  string $out_table
     * @return array
     */
    static public function info_v1(string $out_table)
    {
        $info = self::_info_maps()[$out_table];
        if($info)
        {
            return $info;
        }
        return self::$_table_maps_v1['null'];
    }
    
    /**
     * 
     * @param string $pic_origin_url
     * @return string
     */
    static public function data_mm131_collclass2picclass(string $pic_origin_url)
    {
        $coll = explode('/',$pic_origin_url)[3];
        
//      $list_url = [
//          'mingxing'  => 'http://www.mm131.com/mingxing/list_5_{page}.html',
//          'xinggan'   => 'http://www.mm131.com/xinggan/list_6_{page}.html',
//          'qingchun'  => 'http://www.mm131.com/qingchun/list_1_{page}.html',
//          'xiaohua'   => 'http://www.mm131.com/xiaohua/list_2_{page}.html',
//          'chemo'     => 'http://www.mm131.com/chemo/list_3_{page}.html',
//          'qipao'     => 'http://www.mm131.com/qipao/list_4_{page}.html',
//      ];
        
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
    
    
    /**
     * 
     * @param string $pic_origin_url
     * @return string
     */
    static public function data_99mm_collclass2picclass(string $pic_origin_url)
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


    /**
     * @param string $pic_origin_url
     * 'youhuo'   => 4,   4   => 'youhuo',
       'mote'     => 5,   5   => 'mote',
       'meiru'    => 6,   6   => 'meiru',
       'qingchun' => 7,   7   => 'qingchun',
       'meitui'   => 8,   8   => 'meitui',
       'cosplay'  => 9,   9   => 'cosplay',
     */
    static public function pics_6mm_to_v2(string $pic_origin_url)
    {

    }

    /**
     * @param string $pic_origin_url
        'youhuo'   => 4,   4   => 'youhuo',
        'mote'     => 5,   5   => 'mote',
        'meiru'    => 6,   6   => 'meiru',
        'qingchun' => 7,   7   => 'qingchun',
        'meitui'   => 8,   8   => 'meitui',
        'cosplay'  => 9,   9   => 'cosplay',
     */
    static public function pics_99mm_to_v2(string $pic_origin_url)
    {
        $coll = explode('/',$pic_origin_url)[1];
        $rs =[
            'meitui'   => 8,   // 'meitui'   => 8,
            'qingchun' => 7,   // 'qingchun' => 7,
            'xinggan'  => 4,   // 'youhuo'   => 4,
            'hot'      => 6,   // 'meiru'    => 6,

            'jiepai'   => 7,   // 'qingchun' => 7,
            'mingxing' => 5,   // 'mote'     => 5,
            'wangluo'  => 6,   // 'meiru'    => 6,
        ];
        return $rs[$coll]?$rs[$coll]:'wangluo';
    }

    static public function pics_jpg_to_v2(string $pic_origin_url)
    {

    }

    /**
     * @param string $pic_origin_url
        'youhuo'   => 4, -   4   => 'youhuo',
        'mote'     => 5, -   5   => 'mote',
        'meiru'    => 6,   6   => 'meiru',
        'qingchun' => 7, -   7   => 'qingchun',
        'meitui'   => 8, -  8   => 'meitui',
        'cosplay'  => 9,   9   => 'cosplay',
     */
    static public function pics_mm131_to_v2(string $pic_origin_url)
    {
        $coll = explode('/',$pic_origin_url)[3];
        $rs =[
            'meitui'   => 8,  // 'meitui'   => 8,
            'qingchun' => 7, // 'qingchun' => 7,
            'xinggan'  => 4, // 'youhuo'   => 4,
            'xiaohua'  => 7,// 'qingchun' => 7,

            'chemo'    => 5, // 'mote'     => 5,
            'mingxing' => 5, // 'mote'     => 5,
            'qipao'    => 4, // 'youhuo'   => 4,
        ];
        return $rs[$coll]?$rs[$coll]:'wangluo';
    }

    static public function pics_xgyw_to_v2(string $pic_origin_url)
    {

    }


    /**
     * @param string $origin_url
     */
    static public function v2_dtxt_75_to_75(string $origin_url)
    {
        list(,$tag1,$tag2,) = explode('/',$origin_url);
        $mulu = \cfg\data\article::mulu[$tag2];
        if($mulu)
        {
            return $mulu;
        }
        return \cfg\data\article::mulu['qipa'];
    }

    /**
     * @param string $import_table
     * @param string $file_dir
     * @param string $site_src
     * @param string $coll2pic
     * @param string $data_fun
     * @param string $http_res
     * @return array
     */
    static protected function _info(string $import_table,string $file_dir,string $site_src,string $coll2pic,string $data_fun,string $http_res,string $init_date='',int $init_count=0)
    {
        return [
            'import_table' => $import_table,
            'file_dir'     => $file_dir,
            'site_src'     => $site_src,
            'coll2pic'     => $coll2pic,
            'data_fun'     => $data_fun,
            'http_res'     => $http_res,
            'init_date'    => $init_date ,
            'init_count'   => $init_count,
        ];
    }
}

