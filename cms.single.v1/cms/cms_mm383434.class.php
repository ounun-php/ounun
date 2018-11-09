<?php

namespace cms;

class cms_mm383434 extends \cms\base
{
    /**
     * 首页 美女图吧
     *
     * @return array
     */
    public function mmba_index()
    {
        $mmba = [];
        foreach(\mm_pics::pics_class as $k => $v)
        {
            $mmba[] = $this->db->row("SELECT * FROM  `pic_data` WHERE `pic_class` = ? ORDER BY `is_gaoqing` DESC,`is_goods` DESC,`is_hot` DESC,`add_time` DESC LIMIT 0 , 1;",$k);
        }

        return $mmba;
    }

    /**
     * 首页  最近更新
     *
     * @return array
     */
    public function top_new_index()
    {
        $top_new = [];
        foreach(\mm_pics::pics_class as $k => $v)
        {
            if('wangluo' == $k)
            {
                $rs  = $this->db->data_array("SELECT * FROM  `pic_data` WHERE `pic_class` = ? ORDER BY `add_time` DESC LIMIT 0 , 2;",$k);
                foreach ($rs as $v2)
                {
                    $top_new[] = $v2;
                }
            }else
            {
                $top_new[] = $this->db->row("SELECT * FROM  `pic_data` WHERE `pic_class` = ? ORDER BY `add_time` DESC LIMIT 0 , 1;",$k);
            }
        }
        return $top_new;
    }

    /**
     * 首页 浏览排行
     * @param int $count
     * @param int $start
     * @return array
     */
    public function top_times(int $count,int $start = 0,string $pic_class = '')
    {
        if($pic_class)
        {
            return $this->db->data_array("SELECT * FROM  `pic_data` WHERE `pic_class` = ? ORDER BY `add_time`  DESC LIMIT  {$start} , {$count};",$pic_class);
        }else
        {
            return $this->db->data_array("SELECT * FROM  `pic_data` ORDER BY `pic_times` DESC LIMIT {$start} , {$count};");
        }
    }

    /**
     * 最近更新
     *
     * @param int $count
     * @param int $start
     * @param string $pic_class
     * @return array
     */
    public function top_news(int $count,int $start = 0,string $pic_class = '' )
    {
        if($pic_class)
        {
            return $this->db->data_array("SELECT * FROM  `pic_data` WHERE `pic_class` = ? ORDER BY `add_time` DESC LIMIT  {$start} , {$count};",$pic_class);
        }else
        {
            return $this->db->data_array("SELECT * FROM  `pic_data` ORDER BY `add_time` DESC LIMIT {$start} , {$count};");
        }
    }

    /**
     * 首页 推荐
     * @param int $count
     * @param int $start
     * @return array
     */
    public function recommend(int $count, int $start = 0, string $pic_class = '' )
    {
        if($pic_class)
        {
            return $this->db->data_array("SELECT * FROM  `pic_data` WHERE `pic_class` = ? ORDER BY `is_hot` DESC,`pic_times` DESC LIMIT {$start} , {$count} ;",$pic_class);
        }else
        {
            return $this->db->data_array("SELECT * FROM  `pic_data` ORDER BY `is_hot` DESC,`pic_times` DESC LIMIT  {$start} , {$count};");
        }
        // $rs_recommend   = $this->_db_v->data_array("SELECT * FROM  `pic_data` WHERE `pic_class` = ? ORDER BY `is_hot` DESC,`pic_times` DESC LIMIT 0 , 6;",$pic_class);
    }

    /**
     * 首页 列表
     */
    public function pics_lists_index(int $count = 9, int $start = 1,$mode = 0)
    {
        $index_list          = [];
        foreach(\mm_pics::pics_class as $k => $v)
        {
            $rs              = $this->db->data_array("SELECT * FROM  `pic_data` WHERE `pic_class` = ? ORDER BY `add_time` DESC LIMIT {$start} , {$count};",$k);
            if(1 == $mode)
            {
                $index_list[$k]  = $rs;
            }else
            {
                $index_list[$k]  = [
                    'name' => $v,
                    'one'  => array_slice($rs,0,1)[0],
                    'list' => array_slice($rs,1)
                ];
            }
        }
        return $index_list;
    }

    /**
     * 热门搜索 / 热门标签
     * @param int $count
     * @param int $tag_count
     * @return array
     */
    public function search_keys(int $count = 10,int $tag_count = 2)
    {
        // print_r(['$this->db'=>$this->db]);
//        if($count > 1000)
//        {
            return $this->db->data_array("SELECT `tag`,`tag_count` FROM  `pic_tag` ORDER BY `tag_count` DESC  LIMIT 0 , {$count};");
//        }else
//        {
//            return $this->db->data_array("SELECT `tag`,`tag_count` FROM  `pic_tag` where `tag_count` > {$tag_count} ORDER BY RAND() LIMIT {$count};");
//        }
    }

    /**
     * @param $pic_id
     * @return array
     */
    public function pics_details(int $pic_id)
    {
        return $this->db->row('SELECT * FROM `pic_data` WHERE `pic_id` = ? LIMIT 0 , 1;',$pic_id);
    }

    public function pics_next(int $pic_id)
    {
        return $this->db->row('SELECT * FROM `pic_data` WHERE `pic_id` > ? ORDER BY `pic_id` ASC  LIMIT 0 , 1;',$pic_id);
    }

    public function pics_pre(int $pic_id)
    {
        return $this->db->row('SELECT * FROM `pic_data` WHERE `pic_id` < ? ORDER BY `pic_id` DESC LIMIT 0 , 1;',$pic_id);
    }



    /**
     * @param int $count
     * @param int $start
     * @param string $order
     * @param array $where
     * @return array
     */
    public function pics_lists_simple(int $count = 4, int $start = 0,$order = ' ORDER BY `pic_times` DESC ',array $where = [])
    {
       return $this->db->data_array("SELECT * FROM  `pic_data` {$where['where']} {$order} LIMIT {$start} , {$count};",$where['bind']);
    }


    /**
     * @param string $pic_tag
     * @param int $count
     * @param int $start
     * @param string $order
     * @return array
     */
    public function pics_lists_tags(string $pic_tag, int $count = 12, int $start = 0,string $order = '')
    {
        if($pic_tag)
        {
            $where_tag     = " where `pic_id` in ( SELECT `pic_id` FROM `pic_tag_data` WHERE `tag_id` in ( SELECT `tag_id` FROM `pic_tag` WHERE `tag` in ( ? ) ) ) ";
            $tags          = explode(',',$pic_tag);
        }else
        {
            $where_tag     = '';
            $tags          = null;
        }
        return $this->db->data_array("SELECT * FROM  `pic_data` {$where_tag}   {$order} LIMIT {$start} , {$count};",$tags);
    }

    /**
     * @param string $tag
     * @param int $rows
     * @param string $url
     * @param int $page
     * @param array $page_cfg
     * @return array
     */
    public function pics_lists_tag(string $tag,int $rows,string $url,int $page,array $page_cfg)
    {
        $tag_id     = $this->db->row('SELECT * FROM  `pic_tag` WHERE `tag` = ? LIMIT 0 , 1',$tag);
        if($tag_id)
        {
            $tag_id = (int)$tag_id['tag_id'];
        }
        if($tag_id < 1)
        {
            return [false,404];
        }
        $page_max         = true;
        $page_cfg['rows'] = $rows;
        $table            = '`pic_tag_data`';
        $where            = ['where'=> ' where `tag_id` = ? ','bind' => $tag_id];
        /** 分页 */
        $pg      = new \ounun\page($this->db,$table,$url,$where['where'],$where['bind'],'count(*)',$page_cfg);
        $ps      = $pg->init($page,"",$page_max);
        $rs      = $this->db->data_array("select * from {$table} {$where['where']} limit {$pg->limit_start()},{$rows}",$where['bind']);
        $rs_id   = [];
        foreach($rs as $v)
        {
            $rs_id[$v['pic_id']]=$v['pic_id'];
        }
        $rs_id   = array_keys($rs_id);
        $rs      = $this->db->data_array("select * from `pic_data` WHERE `pic_id` in (?);",$rs_id);

        return [$rs,$ps];
    }


    /**
     * @param $table
     * @param $where
     * @param $order_by
     * @param $rows
     * @param $url
     * @param $page
     * @param $page_cfg
     * @return array
     */
    public function pics_lists(string $table,array $where,string $order_by,int $rows,string $url,int $page,array $page_cfg,bool $page_max)
    {
        $page_cfg['rows'] = $rows;

        $pg      = new \ounun\page($this->db,$table,$url,$where['where'],$where['bind'],'count(*)',$page_cfg);
        $ps      = $pg->init($page,"",$page_max);
        $rs      = $this->db->data_array("select * from {$table} {$where['where']} {$order_by} limit {$pg->limit_start()},{$rows};",$where['bind']);
        // echo $this->db->sql()."\n";
        return [$rs,$ps];
    }

}
