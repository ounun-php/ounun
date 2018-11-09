<?php
namespace module\adm;

class data_export extends \v
{
    /** @var \ounun\mysqli */
    protected $_db_libs = null;
    /** @var \admin\secure */
    protected $_secure  = null;

    /**
     * data_export constructor.
     * @param $mod
     */
    public function __construct($mod)
    {
        // check    -----------------
        $this->_secure          = new \admin\secure(Const_Key_Conn_Private);
        list($check,$error_msg) = $this->_secure->check($_GET,time());
        if(!$check)
        {
            $rs   = ['ret'=>$check,'error'=>$error_msg];
            $this->_secure->outs($rs);
        }

        // adm_purv -----------------
        $libs_key = $_GET['libs_key'];
        if($_GET['libs_key'])
        {
            $libs = $GLOBALS['_scfg']['libs'][$libs_key];
            if($libs && $libs['db'])
            {
                $this->_db_libs   = self::db($libs['db']);
            }
        }
        if(null == $this->_db_libs)
        {
            $rs   = [ 'ret'  => false, 'error'=> '数据库连接失败...' ];
            $this->_secure->outs($rs);
        }
        parent::__construct($mod);
    }


    /**
     * 数据导出  输出接口
     * @param $mod
     */
    public function export($mod)
    {
        if(!$_GET['site_tag'])
        {
            $rs   = ['ret'=>false,'error'=>'提示:$_GET[\'site_tag\']为空...'];
        }elseif (!$_GET['out_table'])
        {
            $rs   = ['ret'=>false,'error'=>'提示:$_GET[\'out_table\']为空...'];
        }

        $site_tag = $_GET['site_tag'];
        $out_table= $_GET['out_table'];
        $Ymd      = ($_GET['Ymd'] && 8 == count($_GET['Ymd']))?$_GET['Ymd']:date('Ymd');
        if('libs_v1' == $_GET['libs_key'])
        {
            $rs   = $this->export_v1($site_tag,$out_table,$Ymd);
        }elseif ('libs_v2' == $_GET['libs_key'])
        {
            $rs   = $this->export_v2($site_tag,$out_table,$Ymd);
        }else
        {
            $rs   = ['ret'=>false,'error'=>'提示:版本错误！'];
        }
        $this->_secure->outs($rs);
    }

    /**
     * @param string $site_tag
     * @param string $out_table
     * @param string $Ymd
     * @return array
     */
    public function export_v1(string $site_tag,string $out_table,string $Ymd = '')
    {
        $info              = \cfg\coll\data_v1::info_v1($out_table);
        $table_import_data = $info['import_table'];
        if(!$table_import_data)
        {
            return ['ret'=>false,'error'=>"提示:\$table:{$out_table}有误错误！\$table_import_data:{$table_import_data}"];
        }
        $table_field    = " `pic_id`,`pic_title`,`pic_tag`,`pic_centent`,`pic_origin_url`,`add_time` ";
        $Ymd            = $Ymd?$Ymd:date('Ymd');
        // 捡查数据
        $bind           = [ 'domain' => $site_tag, 'table'  => $out_table, 'date'   => $Ymd, ];
        $rs             = $this->_db_libs->data_array("SELECT `date_id`,`state` FROM {$table_import_data} WHERE `domain` =:domain and `table` =:table and `date` =:date ;",$bind);
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
                $rs  = $this->_db_libs->data_array("SELECT {$table_field} FROM `{$out_table}` WHERE `pic_id` in (?);",$ids);
                return ['ret'=>true,'ids'=>$ids,'data'=>$rs];
            }else
            {
                return ['ret'=>true,'ids'=>$ids,'data'=>[] ];
            }
        }else
        {
            // 读取数据
            $bind   = [ 'domain' => $site_tag, 'table'  => $out_table ];
            $cc     = rand(2,6);
            $rs     = $this->_db_libs->data_array("SELECT {$table_field} FROM `{$out_table}` WHERE `pic_id` not IN (SELECT `date_id` FROM {$table_import_data} WHERE `domain` = :domain and `table` = :table ) ORDER BY RAND() LIMIT {$cc};",$bind);
            if($rs)
            {
                $ids  = [];
                $bind = [];
                foreach ($rs as $v)
                {
                    $ids[]  = $v['pic_id'];
                    $bind[] = [ 'date'   => $Ymd,  'state'  => 0,   'domain' => $site_tag,  'table'  => $out_table,  'date_id'=> $v['pic_id']  ];
                }
                $this->_db_libs->insert($table_import_data,$bind);
                return ['ret'=>true,'ids'=>$ids,        'data'=>$rs];
            }else
            {
                return ['ret'=>false,'error'=>'没数据了','data'=>[] ];
            }
        }
    }

    /**
     * @param string $site_tag
     * @param string $out_table
     * @param string $Ymd
     * @return array
     */
    public function export_v2(string $site_tag,string $out_table,string $Ymd = '')
    {
        list('import_table' => $table_import_data,
             'init_date'    => $init_date,
             'init_count'   => $init_count)  = \cfg\coll\data_v1::info($out_table,$site_tag);
        if(!$table_import_data)
        {
            return [
                        'ret'   =>  false,
                        'error' => "提示:\$table:{$out_table}有误错误！\$table_import_data:{$table_import_data}"
                   ];
        }

        // 捡查数据
        $Ymd           = $Ymd?$Ymd:date('Ymd');
        $bind          = [ 'domain' => $site_tag, 'table'  => $out_table, 'date'   => $Ymd, ];

        $ids           = [];
        if($init_date && $init_count && $init_date == $Ymd)
        {
            $rs        = $this->_db_libs->fetch_assoc("SELECT COUNT( `date_id`) as `cc`,`state` FROM {$table_import_data} WHERE `domain` =:domain and `table` =:table  GROUP by `state`;",$bind,'state');
            $state_1   = (int)$rs[1]['cc'];
            $state_0   = (int)$rs[0]['cc'];
            $state_cc  = $state_1 + $state_0;
            //
            if($state_cc < $init_count)
            {
                if($state_0 > 0)
                {
                    $rs = $this->_db_libs->data_array("SELECT `date_id`,`state` FROM {$table_import_data} WHERE `domain` =:domain and `table` =:table and `state` = 0 ;",$bind);
                    if($rs && is_array($rs))
                    {
                        foreach ($rs as $v)
                        {
                            if($v && $v['date_id'] && 0 == $v['state'])
                            {
                                $ids[] = $v['date_id'];
                            }
                        }
                    }
                }
                if($ids && is_array($ids) )
                {
                    $is_loop  = true;
                    $is_rand  = false;
                }else
                {
                    $is_loop  = true;
                    $is_rand  = true;
                }
            }else
            {
                $is_loop = false;
                $is_rand = false;
            }
        }else
        {
            $is_loop   = false;
            $is_rand   = true;
            $rs        = $this->_db_libs->data_array("SELECT `date_id`,`state` FROM {$table_import_data} WHERE `domain` =:domain and `table` =:table and `date` =:date ;",$bind);
            if($rs && is_array($rs))
            {
                foreach ($rs as $v)
                {
                    if($v && $v['date_id'] && 0 == $v['state'])
                    {
                        $ids[] = $v['date_id'];
                    }
                }
            }
        }


        $table_field   = " `data_id`,`title`,`tag`,`dataorigin`,`origin_url`,`time_add` ";
        if($ids && is_array($ids) )
        {
            $rs        = $this->_db_libs->data_array("SELECT {$table_field} FROM `{$out_table}` WHERE `data_id` in (?);",$ids);
            return ['ret'=>true,'ids'=>$ids,'loop'=>$is_loop,'data'=>$rs];
        }elseif ($is_rand)
        {
            // 读取数据
            $bind   = [ 'domain' => $site_tag, 'table'  => $out_table ];
            $cc     = rand(2,6);
            $rs     = $this->_db_libs->data_array("SELECT {$table_field} FROM `{$out_table}` WHERE `data_id` not IN (SELECT `date_id` FROM {$table_import_data} WHERE `domain` = :domain and `table` = :table ) ORDER BY RAND() LIMIT {$cc};",$bind);
            if($rs)
            {
                $ids  = [];
                $bind = [];
                foreach ($rs as $v)
                {
                    $ids[]  = $v['data_id'];
                    $bind[] = [ 'date' => $Ymd, 'state' => 0, 'domain' => $site_tag,'table'=> $out_table, 'date_id'=> $v['data_id']  ];
                }
                $this->_db_libs->insert($table_import_data,$bind);
                return ['ret'=>true ,'ids'=>$ids,'loop'=>$is_loop,'data'=>$rs];
            }else
            {
                return ['ret'=>false,'ids'=>$ids,'loop'=>$is_loop,'data'=> [],'error'=>'没数据了',];
            }
        }
        return ['ret'=>false,'ids'=>$ids,'loop'=>$is_loop,'data'=>[]];


//        if($rs && is_array($rs))
//        {
//            $ids  = [];
//            foreach ($rs as $v)
//            {
//                if($v && $v['date_id'] && 0 == $v['state'])
//                {
//                    $ids[] = $v['date_id'];
//                }
//            }
//            if($ids)
//            {
//                $rs  = $this->_db_libs->data_array("SELECT {$table_field} FROM `{$out_table}` WHERE `data_id` in (?);",$ids);
//                return ['ret'=>true,'ids'=>$ids,'data'=>$rs];
//            }else
//            {
//                return ['ret'=>true,'ids'=>$ids,'data'=>[] ];
//            }
//        }else
//        {
//            // 读取数据
//            $bind   = [ 'domain' => $site_tag, 'table'  => $out_table ];
//            $cc     = rand(2,6);
//            $rs     = $this->_db_libs->data_array("SELECT {$table_field} FROM `{$out_table}` WHERE `data_id` not IN (SELECT `date_id` FROM {$table_import_data} WHERE `domain` = :domain and `table` = :table ) ORDER BY RAND() LIMIT {$cc};",$bind);
//            if($rs)
//            {
//                $ids  = [];
//                $bind = [];
//                foreach ($rs as $v)
//                {
//                    $ids[]  = $v['data_id'];
//                    $bind[] = [ 'date'   => $Ymd,  'state'  => 0,   'domain' => $site_tag,  'table'  => $out_table,  'date_id'=> $v['data_id']  ];
//                }
//                $this->_db_libs->insert($table_import_data,$bind);
//                return ['ret'=>true,'ids'=>$ids,        'data'=>$rs];
//            }else
//            {
//                return ['ret'=>false,'error'=>'没数据了','data'=>[] ];
//            }
//        }
    }

    /**
     * 数据导出  -- 更新
     * @param $mod
     */
    public function export_dateup($mod)
    {
        if(!$_GET['site_tag'])
        {
            $rs   = ['ret'=>false,'error'=>'提示:$_GET[\'site_tag\']为空...' ];
        }elseif (!$_GET['out_table'])
        {
            $rs   = ['ret'=>false,'error'=>'提示:$_GET[\'out_table\']为空...'];
        }elseif (!$_GET['ids'])
        {
            $rs   = ['ret'=>false,'error'=>'提示:$_GET[\'ids\']为空...'      ];
        }

        $site_tag = $_GET['site_tag'];
        $out_table= $_GET['out_table'];
        $ids      = explode('-', $_GET['ids']);
        // print_r(['$ids'=>$ids]);
        if('libs_v1' == $_GET['libs_key'])
        {
            $rs   = $this->export_dateup_v1($site_tag,$out_table,$ids);
        }elseif ('libs_v2' == $_GET['libs_key'])
        {
            $rs   = $this->export_dateup_v2($site_tag,$out_table,$ids);
        }else
        {
            $rs   = ['ret'=>false,'error'=>'提示:版本错误！'];
        }
        $this->_secure->outs($rs);
    }

    /**
     * @param string $site_tag
     * @param string $out_table
     * @param array $ids
     * @return array
     */
    public function export_dateup_v1(string $site_tag = '',string $out_table = '',array $ids = [])
    {
        $info              = \cfg\coll\data_v1::info_v1($out_table);
        $table_import_data = $info['import_table'];
        if(!$table_import_data)
        {
            return [ 'ret' => false, 'error' => "提示:\$table:{$out_table}有误错误！\$table_import_data:{$table_import_data}"  ];
        }

        $rs         = 0;
        if($ids)
        {
            $bind   = [ 'domain' => $site_tag,  'table'  => $out_table,   'date_id' => $ids, ];
            $rs     = $this->_db_libs->update($table_import_data,['state'=>1],"`domain` =:domain and `table` =:table and `date_id` in (:date_id) ",$bind);
        }
        return ['ret'=>true,'data'=>$rs];
    }

    /**
     * @param string $site_tag
     * @param string $out_table
     * @param array  $ids
     */
    public function export_dateup_v2(string $site_tag = '',string $out_table = '',array $ids = [])
    {
        $info              = \cfg\coll\data_v1::info_v1($out_table);
        $table_import_data = $info['import_table'];
        if(!$table_import_data)
        {
            return [ 'ret' => false, 'error' => "提示:\$table:{$out_table}有误错误！\$table_import_data:{$table_import_data}"  ];
        }

        $rs         = 0;
        if($ids)
        {
            $bind   = [ 'domain' => $site_tag,  'table'  => $out_table,   'date_id' => $ids, ];
            $rs     = $this->_db_libs->update($table_import_data,['state'=>1],"`domain` =:domain and `table` =:table and `date_id` in (:date_id) ",$bind);
        }
        return ['ret'=>true,'data'=>$rs];
    }
}