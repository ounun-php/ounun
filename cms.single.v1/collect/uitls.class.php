<?php
namespace collect;


class uitls
{
    /**
     * 数据
     */
    public static function fields($pic_id,$pic_title,$pic_origin_url,$pic_ext,$site_sub_key)
    {
        $tags   = \mm_pics::tag($pic_title);
        return [
            'pic_id'            => $pic_id,
            'pic_goods'         => 0,
            'pic_collect_count' => 0,
            'pic_title'         => $pic_title,
            'pic_tag'           => json_encode($tags,JSON_UNESCAPED_UNICODE),
            'pic_centent'       => '',
            'pic_ext'           => json_encode($pic_ext,JSON_UNESCAPED_UNICODE),
            'pic_origin_url'    => $pic_origin_url,

            'site_name'         => '',
            'site_class_key'    => '',
            'site_class_name'   => '',
            'site_sub_key'      => $site_sub_key,
            'site_sub_name'     => '',
            'site_ext_key'      => '',
            'site_ext_name'     => '',

            'is_qiniu'          => 0,
            'is_wget'           => 0,
            'is_done'           => 0,
            'update_interval'   => 0,
            'update_time'       => 0,
            'update_count'      => 0,

            'add_time'          => time(),
        ];
    }

    public static function db_insert(\ounun\mysqli $db,string $table, array $data)
    {
        // $rs =  $db->row("SELECT * FROM {$table} where `pic_id` = :pic_id limit 0,1;",$data);
        $rs    =  self::db_check($db,$table,$data['pic_id']);
        if(!$rs)
        {
            $db->insert($table,$data);
            $i_id = self::db_check($db,$table,$data['pic_id']);
            if($i_id)
            {
                echo "ok ---------------------->  插入成功 pic_id:{$data['pic_id']}\n";
            }else{
                echo "sql:".$db->sql()."\n";
                echo "error --->  插入失败 pic_id:{$data['pic_id']}\n";
            }
        }else
        {
            echo "error --->  已存在 pic_id:{$data['pic_id']}\n";
        }
    }

    public static function db_check(\ounun\mysqli $db,string $table, int $pic_id)
    {
        $rs =  $db->row("SELECT * FROM {$table} where `pic_id` = :pic_id limit 0,1;",['pic_id'=>$pic_id]);
        if($rs && $rs['pic_id'])
        {
           return $rs;
        }
        return false;
    }



    /**
     * html 解析
     * @param string $str
     * @param string $pattern  //  '/\{\{(\d*),(\d*)\},\{(\d*),(\d*)\}\}/'
     * @param string $fields   //
     * @return array
     */
    static public function html_decode($str,$pattern,$field)
    {
        $rs      = array();
        $matches = array(); //     1     2         3     4
        //$pattern = '/\{\{(\d*),(\d*)\},\{(\d*),(\d*)\}\}/';
        //$pattern = '/\{\{(\d*),(\d*)\},\{(\d*),(\d*)\}\}/';
        $fields	 = explode(',', $field);
        preg_match_all($pattern, $str, $matches, PREG_SET_ORDER);
        foreach ($matches as $v)
        {
            $vs   = array();
            foreach ($fields as $k=>$v2)
            {
                if($v[$k+1] !== "")
                {
                    $vs[$v2] = $v[$k+1];
                }
            }
            if($vs)
            {
                $rs[] = $vs;
            }
        }
        return $rs;
    }

    /**
     * @param $url
     * @param $referer
     * @param string $timeout
     * @return mixed
     */
    static public function curls($url,$referer,$timeout = '10')
    {
        // 1. 初始化
        $ch = curl_init();
        // 2. 设置选项，包括URL
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/35.0.1916.153 Safari/536.35'); // 模拟用户使用的浏览器
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);    // 自动设置Referer
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_REFERER, $referer);
        // 3. 执行并获取HTML文档内容
        $info = curl_exec($ch);
        // 4. 释放curl句柄
        curl_close($ch);

        return $info;
    }
//    /**
//     * 插入单条数据
//     */
//    public static function db_update(\ounun\mysqli $db,string $table, array $data)
//    {
//        $pic_id  = (int)$data['pic_id'];
//        unset($data['pic_id']);
//        if($pic_id)
//        {
//            $db->update($table,$data,' `pic_id` = ? ',$pic_id);
//            // echo "{$this->_db->getsql()}\n";
//            return $pic_id;
//        }else
//        {
//            $pic_origin_url = $data['pic_origin_url'];
//            if($pic_origin_url)
//            {
//                $rs         = $db->row("SELECT * FROM  {$table} WHERE `pic_origin_url` = ? LIMIT 0 , 1;",$pic_origin_url);
//                if($rs && $rs['pic_id'])
//                {
//                    $pic_id = (int)$rs['pic_id'];
//                    if(!$rs['site_name'])
//                    {
//                        $db->update($table,$data,' `pic_id` = ? ',$pic_id);
//                    }elseif('www.6mm.cc' == $data['site_name'] && $data['site_sub_key'])
//                    {
//                        $data2 = ['site_sub_key'=>$data['site_sub_key'],'site_sub_name'=>$data['site_sub_name']];
//                        $db->update($table,$data2,' `pic_id` = ? ',$pic_id);
//                    }
//                    $db->add($table,['pic_collect_count'=>1],' `pic_id` = ? ',$pic_id);
//                }else
//                {
//                    $pic_id = $db->insert($table,$data);
//                }
//            }
//        }
//        return $pic_id;
//    }
//
//    /**
//     * 上传图片
//     */
//    protected function _put_img($local_filename,$dir,$filename)
//    {
//        $filename = $dir.$filename;
//        echo "\$local_filename:{$local_filename} size:".filesize($local_filename)."\n";
//        echo "\$filename:{$filename}\n";
//
//        //$rs       = $this->_qiniu->del_file($filename);
//        //var_export($rs);
//        //$rs       = $this->_qiniu->put_file($filename,$local_filename,1);
//        //var_export($rs);
//    }
}
