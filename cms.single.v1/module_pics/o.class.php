<?php
namespace module_pics;


class o extends \v
{
    /**
     * 取数据
     * https://adm.383434.com/zrun_api/data/pics_99mm/mm-erldoc-com/
     * https://adm.383434.com/zrun_api/data/pics_99mm/www-383434-com/
     *
     * 更新数据
     * https://adm.383434.com/zrun_api/data/pics_99mm/mm-erldoc-com/436-466-641-888-1640-1826/
     * https://adm.383434.com/zrun_api/data/pics_99mm/www-383434-com/436-466-641-888-1640-1826/
     *
     * 数据接口  输出接口
     *
     * @param $mod
     */

    public function data($mod)
    {
        $table             = trim($mod[1]);
        $domain            = str_replace('-','.',$mod[2]);
        $ids               = $mod[3]?explode('-',trim($mod[3])):[];

        $table_import_data = "`import_data`";
        $table_field       = " `pic_id`,`pic_title`,`pic_tag`,`pic_centent`,`pic_origin_url`,`add_time` ";
        $db                = self::db('libs_collect');
        $db->active();
        if($ids)
        {
            $this->_dateup($db,$table,$domain,$table_import_data,$ids);
        }else
        {
            $Ymd           = date('Ymd');
            $this->_data($db,$table,$table_field, $domain, $Ymd, $table_import_data);
        }
    }


    public function _dateup(\ounun\mysqli $db,string $table,string $domain,string $table_import_data,array $ids)
    {
        $rs         = 0;
        if($ids)
        {
            $bind   = [
                'domain' => $domain,
                'table'  => $table,
                'date_id' => $ids,
            ];
            $rs     =  $db->update($table_import_data,['state'=>1],"`domain` =:domain and `table` =:table and `date_id` in (:date_id) ",$bind);
        }
        exit(json_encode(['ret'=>1,'c'=>$rs],JSON_UNESCAPED_UNICODE));
    }


    private function _data(\ounun\mysqli $db,string $table,string $table_field,string $domain,string $Ymd,string $table_import_data)
    {
        $db     = self::db('libs_collect');

        // 捡查数据
        $bind   = [
            'domain' => $domain,
            'table'  => $table,
            'date'   => $Ymd,
        ];
        $rs     = $db->data_array("SELECT `date_id`,`state` FROM {$table_import_data} WHERE `domain` =:domain and `table` =:table and `date` =:date ;",$bind);
        if($rs && is_array($rs))
        {
            $ids  = [];
            foreach ($rs as $v)
            {
                if($v && $v['date_id'] && 0 == $v['state'])
                {
                    $ids[] = $v['date_id'];
                }
            }
            if($ids)
            {
                $rs  = $db->data_array("SELECT {$table_field} FROM `{$table}` WHERE `pic_id` in (?);",$ids);
                exit(json_encode(['ret'=>1,'ids'=>$ids,'data'=>$rs],JSON_UNESCAPED_UNICODE));
            }else
            {
                exit(json_encode(['ret'=>1,'ids'=>$ids,'data'=>[]],JSON_UNESCAPED_UNICODE));
            }
        }else
        {
            // 读取数据
            $bind   = [
                'domain' => $domain,
                'table'  => $table
            ];
            $cc   = rand(2,6);
            $rs   = $db->data_array("SELECT {$table_field} FROM `{$table}` WHERE `pic_id` not IN (SELECT `date_id` FROM {$table_import_data} WHERE `domain` = :domain and `table` = :table ) ORDER BY RAND() LIMIT {$cc};",$bind);
            if($rs)
            {
                $ids  = [];
                $bind = [];
                foreach ($rs as $v)
                {
                    $ids[]  = $v['pic_id'];
                    $bind[] = [
                        'date'   => $Ymd,
                        'state'  => 0,
                        'domain' => $domain,
                        'table'  => $table,
                        'date_id'=> $v['pic_id']
                    ];
                }
                $db->insert($table_import_data,$bind);
                exit(json_encode(['ret'=>1,'ids'=>$ids,'data'=>$rs],JSON_UNESCAPED_UNICODE));
                //
                // echo $db->sql()."\n";
            }else
            {
                // print_r(['$table'=>$table,'$domain'=>$domain]);
                exit(json_encode(['ret'=>0,'error'=>'没数据了','data'=>[]],JSON_UNESCAPED_UNICODE));
            }
        }
    }
    // -----------------------
}