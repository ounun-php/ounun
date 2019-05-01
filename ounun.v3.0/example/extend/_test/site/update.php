<?php
namespace ounun\cmd\task\site_base;

use ounun\cmd\task\manage;
use ounun\cmd\task\struct;
use ounun\config;
use ounun\mvc\model\admin\secure;

abstract class update extends _site
{

    public function __construct(struct $task_struct, string $tag = '', string $tag_sub = '')
    {
        $this->_tag       = 'update';
        $this->_tag_sub   = '';

        parent::__construct($task_struct, $tag, $tag_sub);
    }

    /**
     * 执行任务
     * @param array $input
     * @param int $mode
     * @param bool $is_pass_check
     */
    public function execute(array $input = [], int $mode = manage::Mode_Dateup,bool $is_pass_check = false)
    {
        try
        {
            $this->_logs_state = manage::Logs_Succeed;
            // $this->url_refresh();
            // print_r(['$paras'=>$paras,'_args'=>$this->_args]);
            // $site_tag = \scfg::$app;
            // print_r(['$paras'=>$paras,'_args'=>$this->_args,'$libs_key'=>$libs_key,'$in_table'=>$in_table,'$out_table'=>$out_table,'\scfg::$app'=>\scfg::$app]);
            // print_r(['$libs_key'=>$libs_key,'$in_table'=>$in_table,'$out_table'=>$out_table]);
            $this->data($this->_task_struct->extend, config::$app_name);
            // $this->msg("Successful update:{$this->_args}");
        } catch (\Exception $e)
        {
            $this->_logs_state = manage::Logs_Fail;
            $this->msg($e->getMessage());
            $this->msg("Fail update");
        }
    }


    /**
     * @param string $libs_key
     * @param string $in_table
     * @param string $out_table
     * @param string $site_tag
     */
    public function data(array $exts,string $site_tag)
    {
        $secure             = new secure(config::$app_key_communication);
        $url_root           = Environment ?"http://adm2.moko8.com/api/export/":"https://adm.moko8.com/api/export/";
        $this->_logs_state  = manage::Logs_Succeed;
        if('libs_v1' == $exts[0])
        {
            list($libs_key,$in_table,$out_table) = $exts;
            $data = [
                        'libs_key'  => $libs_key,
                        'site_tag'  => $site_tag,
                        'out_table' => $out_table,
                    ];
            list('ret'=>$ret,'data'=>$data,) = $secure->wget($url_root,$data);
            $this->libs_v1($libs_key,$site_tag,$in_table,$out_table,$data);
        }elseif('libs_v2' == $exts[0])
        {
            list($libs_key,$in_table,$out_libs_key,$out_table) = $exts;
         // $data = [ 'libs_key'  => $libs_key, 'site_tag'  => $site_tag,'out_libs_key'=>$out_libs_key, 'out_table' => $out_table, ];
            $data = [
                        'libs_key'  => $out_libs_key,
                        'site_tag'  => $site_tag,
                        'out_table' => $out_table,
                    ];
            list('ret'=>$ret,'data'=>$data,) = $secure->wget($url_root,$data);
            $this->libs_v2($libs_key,$site_tag,$in_table,$out_libs_key,$out_table,$data);
        }else
        {
            $paras_count = count($exts);
            if(5 == $paras_count && 'libs_v2' == $exts[1])
            {
                $funs = "libs_v2_{$exts[0]}";
                if(method_exists($this,$funs))
                {
                    // print_r(['$exts'=>$exts]);
                    list(,$libs_key,$in_table,$out_libs_key,$out_table) = $exts;
                    $paras_get = ['libs_key'=>$out_libs_key,'site_tag'=>$site_tag,'out_table'=>$out_table, ];
                    do
                    {
                        list('ret'=>$ret,'data'=>$data,'loop'=>$loop) = $secure->wget($url_root,$paras_get);
                        if($ret && $data)
                        {
                            $this->$funs($libs_key,$site_tag,$in_table,$out_libs_key,$out_table,$data);
                        }
                    }while($loop);
                }else
                {
                    $this->_logs_state  = manage::Logs_Fail;
                    $this->msg("Fail funs error:{$funs}");
                }
            }else
            {
                $this->_logs_state  = manage::Logs_Fail;
                $this->msg("Fail libs_key error value:{$exts[0]}");
            }
        }
    }

    
    /**
     * 更新网站
     * @param string $in_table
     * @param string $out_table
     * @param array  $datas
     * @return array [] ids数组ID
     */
    protected function libs_v1(string $libs_key,string $site_tag,string $in_table,string $out_table,array $datas = [])
    {
        // print_r(['$libs_key'=>$libs_key,'$site_tag'=>$site_tag,'$in_table'=>$in_table,'$out_table'=>$out_table,'$data'=>$datas]);
        // 'import_table' => $import_table,  
        list('file_dir' => $file_dir, 'site_src' => $site_src, 'coll2pic' => $coll2pic) = \cfg\coll\data_v1::info_v1($out_table);
        if(!$file_dir )
        {
            $this->_logs_state  = manage::Logs_Fail;
            $this->msg("Fail file_dir:null");
            return ;
        }elseif (!$site_src)
        {
            $this->_logs_state  = manage::Logs_Fail;
            $this->msg("Fail site_src:null");
            return ;
        }elseif (!$coll2pic)
        {
            $this->_logs_state  = manage::Logs_Fail;
            $this->msg("Fail coll2pic:null");
            return ;
        }
        
        $ids             = [];
        foreach ($datas as $data)
        {
            $site_name   = "{$site_src}/{$data['pic_id']}";
            $rs          = $this->_db->row("SELECT `pic_id` FROM `{$in_table}` where `site_name` = :site_name limit 1;",['site_name'=>$site_name]);
            // echo $this->_db->sql()."\n";
            if(!$rs)
            {
                $data['pic_centent'] = json_decode($data['pic_centent'],true);
                $pic_tag   = json_decode($data['pic_tag'],true);
                $pic_class = \cfg\coll\data_v1::$coll2pic($data['pic_origin_url']);
                $pic_tag   = implode(',',$pic_tag);
                $pic_ext   = $data['pic_centent'];
                $pic_exts  = $pic_ext['data'];
                $pic_exts2 = [];
                if($pic_exts && is_array($pic_exts))
                {
                    foreach ($pic_exts as $k=>$v)
                    {
                        $pic_exts2[$k] = "{$file_dir}/{$v}";
                    }
                }
                $is_gaoqing = rand ( 0 , 10000 ) < 1000?1:0;
                $is_hot     = rand ( 0 , 20000 ) < 1000?1:0;
                $is_goods   = rand ( 0 , 10000 ) < 1000?1:0;
                $bind    = [
                    'pic_class'   => $pic_class,
                    'pic_title'   => $data['pic_title'],
                    'pic_tag'     => $pic_tag,
                    'pic_centent' => \mm_pics::pics_class[$pic_class].", ".$data['pic_title'].", ".$pic_tag,
                    'pic_ext'     => $pic_exts2,//$pic_exts,
                    'pic_cover'   => 0,
                    'pic_thum'    => 0,
                    'is_gaoqing'  => $is_gaoqing,
                    'is_hot'      => $is_hot,
                    'is_goods'    => $is_goods,
                    'is_done'     => 1,
                    'site_name'   => $site_name
                ];
                $pic_id      = $this->v1_data($bind);
                if($pic_id)
                {
                    $ids[]   = $data['pic_id'];
                }
            }else
            {
                $ids[]       = $data['pic_id'];
            }
        }
        // ====================================
        if($ids) {
            $table_tag      = ' `pic_tag` ';
            $table_tag_data = ' `pic_tag_data` ';
            $this->v1_tag_count($table_tag,$table_tag_data);
            $this->msg("ids:".json_encode($ids,true));
            $this->v1_libs_complete($libs_key,$site_tag,$out_table,$ids);
        }
    }

    protected function v1_libs_complete(string $libs_key,string $site_tag,string $out_table,array $ids = [])
    {
        $data = [
            'libs_key'  => $libs_key,
            'site_tag'  => $site_tag,
            'out_table' => $out_table, 
            'ids'       => implode('-',$ids),
        ];
        $secure         = new \admin\secure(Const_Key_Conn_Private);
        $url_root       = IsDebug?"http://adm2.moko8.com/api/export_dateup/":"https://adm.moko8.com/api/export_dateup/";
        list('ret'=>$ret,'data'=>$data) = $secure->wget($url_root,$data);
        if($ret && $data) {
            $this->_logs_state  =manage::Logs_Succeed;
            $this->msg("update {$out_table} coll:".json_encode($data));
        }else {
            $this->_logs_state  = manage::Logs_Fail;
            $this->msg("Fail {$out_table} update:".json_encode($data));
        }
    }
    
    protected function v1_data($data)
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
        $pic_id   = $this->v1_data_save($bind);
        $this->msg("news pic_id:{$pic_id} ok!!!");
        return $pic_id;
    }
    /** */
    protected function v1_data_save(array $data,string $table_tag = '`pic_tag`',string $table_tag_data = '`pic_tag_data`')
    {
        $pic_id  = $this->_db->insert('`pic_data`',$data);
        //
        $tag     = $data['pic_tag'];
        $tag_ids = [];
        if($tag) {
            $tag = explode(',',$tag);
            foreach($tag as $v) {
                $rs = $this->_db->row('SELECT `tag_id` FROM  `pic_tag` where `tag` = ? LIMIT 0 , 1;',$v);
                if($rs && $rs['tag_id']) {
                    $tag_bind           = ['tag'=>$v,'tag_id'=> $rs['tag_id']];
                }else {
                    $tag_bind           = ['tag'=>$v];
                    $tag_bind['tag_id'] = $this->_db->insert('`pic_tag`',$tag_bind);
                }
                $tag_ids[]              = $tag_bind;
            }
        }
        //echo "\$tag_ids\n";
        //print_r($tag_ids);echo "\n";
        $tag_datas = [];
        if($tag_ids && is_array($tag_ids)) {
            foreach($tag_ids as $v) {
                $tag_datas[] = ['pic_id'=>$pic_id,'tag_id'=>$v['tag_id']];
            }
        }
        // echo "\$tag_datas\n";
        // print_r($tag_datas);echo "\n";
        if($tag_datas) {
            $this->_db->insert('`pic_tag_data`',$tag_datas);
        }
        return $pic_id;
    }
    
    /** 刷新tag */
    protected function v1_tag_count(string $table_tag = '`pic_tag`',string $table_tag_data = '`pic_tag_data`')
    {
        $rs = $this->_db->data_array("SELECT  `tag_id` , COUNT( * ) AS  `tag_count` FROM  {$table_tag_data} GROUP BY  `tag_id` ");
        if($rs) {
            foreach($rs as $v) {
                $this->_db->update($table_tag,['tag_count'=>$v['tag_count']],' `tag_id` = ? ',$v['tag_id']);
            }
        }
    }
    
    /** */
    protected function libs_v2(string $libs_key,string $site_tag,string $in_table,string $out_libs_key,string $out_table,array $datas = [])
    {
        // print_r(['$libs_key'=>$libs_key,'$site_tag'=>$site_tag,'$in_table'=>$in_table,'$out_libs_key'=>$out_libs_key,'$out_table'=>$out_table,'$datas'=>$datas]);
        $ids         = [];
        $data_ids    = [];
        if('libs_v1' == $out_libs_key) {
            foreach ($datas as $v) {
                $coll2pic         = "{$out_table}_to_v2";
                $mod_id           = \site_cfg::mod_pics;
                $v['category_id'] = \cfg\coll\data_v1::$coll2pic($v['pic_origin_url']);
                $data_id          = \uitls\pics\comm::pic_install($this->_db,$v,$out_table,$v['pic_id'],$mod_id);
                if($data_id) {
                    $this->msg("news data_id:{$data_id} ok!!!");
                    $data_ids[]   = $data_id;
                    $ids[]        = $v['pic_id'];
                }
            }
        }
        // ====================================
        if($ids) {
            if($mod_id) {
                $table_tag      = ' `tag_stat` ';
                $table_tag_data = ' `tag_idx` ';
                $this->v2_tag_count($mod_id,$table_tag,$table_tag_data);
            }
            $this->msg("ids:".json_encode($ids,true));
            $this->v2_libs_complete($out_libs_key,$site_tag,$out_table,$ids);
        }
    }

    protected function libs_v2_75(string $libs_key,string $site_tag,string $in_table,string $out_libs_key,string $out_table,array $datas = [])
    {
        // $info = \cfg\coll\data_v1::info($out_table,$site_tag);
        // print_r(['$info'=>$info]);
        list('file_dir' => $file_dir, 'site_src' => $site_src, 'coll2pic' => $coll2pic) = \cfg\coll\data_v1::info($out_table,$site_tag);
        if(!$file_dir ) {
            $this->_logs_state  = manage::Logs_Fail;
            $this->msg("Fail file_dir:null");
            return ;
        }elseif (!$site_src) {
            $this->_logs_state  = manage::Logs_Fail;
            $this->msg("Fail site_src:null");
            return ;
        }elseif (!$coll2pic) {
            $this->_logs_state  = manage::Logs_Fail;
            $this->msg("Fail coll2pic:null");
            return ;
        }
        // print_r(['$libs_key'=>$libs_key,'$site_tag'=>$site_tag,'$in_table'=>$in_table,'$out_libs_key'=>$out_libs_key,'$out_table'=>$out_table,'$datas'=>$datas]);
        $ids         = [];
        $data_ids    = [];
        $mod_id      = 0;
        if('libs_v2' == $out_libs_key) {
            $mod_id  = \cfg\data\article::mod_news;
            foreach ($datas as $v) {
                // print_r(['$v'=>$v]);
                list('category_id'=>$category_id,'category_sub'=>$category_sub) = \cfg\coll\data_v1::$coll2pic($v['origin_url']);
                $v['category_id'] = $category_id;
                $v['category_sub']= $category_sub;

                // print_r(['$v'=>$v]);
                $data_id          = \uitls\article\comm::data_install($this->_db,$v,$out_table,$v['data_id'],$mod_id);
                if($data_id) {
                    $this->msg("news data_id:{$data_id} ok!!!");
                    $data_ids[]   = $data_id;
                    $ids[]        = $v['data_id'];
                }
            }
        }
        // ====================================
        if($ids) {
            if($mod_id) {
                $table_tag      = ' `tag_stat` ';
                $table_tag_data = ' `tag_idx` ';
                $this->v2_tag_count($mod_id,$table_tag,$table_tag_data);
            }
            $this->msg("ids:".json_encode($ids,true));
            $this->v2_libs_complete($out_libs_key,$site_tag,$out_table,$ids);
        }
    }

    protected function v2_libs_complete(string $libs_key,string $site_tag,string $out_table,array $ids = [])
    {
        $data = [
            'libs_key'  => $libs_key,
            'site_tag'  => $site_tag,
            'out_table' => $out_table,
            'ids'       => implode('-',$ids),
        ];
        $secure         = new secure(config::$app_key_communication);
        $url_root       = Environment?"http://adm2.moko8.com/api/export_dateup/":"https://adm.moko8.com/api/export_dateup/";
        list('ret'=>$ret,'data'=>$data) = $secure->wget($url_root,$data);
        if($ret && $data) {
            $this->_logs_state  = manage::Logs_Succeed;
            $this->msg("update {$out_table} coll:".json_encode($data));
        }else {
            $this->_logs_state  = manage::Logs_Fail;
            $this->msg("Fail {$out_table} update:".json_encode($data));
        }
    }

    /** 刷新tag */
    protected function v2_tag_count(int $mod_id ,string $table_tag_stat = '`tag_stat`',string $table_tag_data = '`tag_idx`')
    {
        $rs = $this->_db->query("SELECT  `tag_id` , COUNT(`data_id`) AS  `count` FROM  {$table_tag_data} where `mod_id` = {$mod_id} GROUP BY  `tag_id` ")->column_all();
        if($rs) {
            foreach($rs as $v) {
                // print_r(['$v'=>$v]);
                $bind = ['count'=>$v['count'],'star'=>1];
                $this->_db->table($table_tag_stat)->duplicate($bind)->insert(array_merge(['mod_id'=>$mod_id,'tag_id'=>$v['tag_id']],$bind));
                // echo $this->_db->sql()."\n";
                // $this->_db->update($table_tag_stat,['count'=>$v['count']],' `tag_id` = ? ',$v['tag_id']);
            }
        }
    }
}
