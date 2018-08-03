<?php
namespace cms;

class cms_pics extends \cms\base
{
    /** @var \ounun\mysqli */
    public $db = null;


    /**
     * 热搜美女
     * @param int $count
     */
    public function star_lists_simple(int $count = 12, $order = " ORDER BY `data` .`times` DESC ")
    {
        $bind = ['mod_id' => \site_cfg::mod_star ];
        $rs0  =  $this->db->data_array("SELECT `data` .`data_id`, `data` .`title`, `data` .`times`,`data` .`exts`, `data_star`.`pinyin` FROM `data` ,`data_star` WHERE `data` .`mod_id` = :mod_id and `data` .`data_id` = `data_star` .`star_id` {$order}  LIMIT 0 , {$count};",$bind);
        $rs   = [];
        foreach ($rs0 as $v){
            $v['exts'] = json_decode($v['exts'],true);
            $rs[]      = $v;
        }
        // echo $this->db->sql()."\n";
        return $rs;
    }


    /**
     * @param $star_name
     */
    public function star_details($star_name_pinyin, $is_name_pinyin = 'name',$fields = ' `data` .`data_id`, `data` .`title`, `data` .`times`,`data` .`exts`, `data_star`.`pinyin` ')
    {
        if($is_name_pinyin == 'name'){
            $where      = " and `data` .`title` = :title ";
            $bind       = ['mod_id' => \site_cfg::mod_star,'title'=>$star_name_pinyin];
        }else{
            $where      = " and `data_star`.`pinyin` = :pinyin ";
            $bind       = ['mod_id' => \site_cfg::mod_star,'pinyin'=>$star_name_pinyin];
        }
        $rs         = $this->db->row("SELECT {$fields} FROM `data` ,`data_star` WHERE `data` .`mod_id` = :mod_id and `data` .`data_id` = `data_star` .`star_id`  {$where}  LIMIT 0 , 1;",$bind);
        $rs['exts'] = json_decode($rs['exts'],true);
        //  echo $this->db->sql()."<br />\n";
        return $rs;
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
    public function pics_lists(string $table,string $where,$where_bind,string $order_by,int $rows,string $url,int $page,array $page_cfg,bool $page_max,string $fields = ' * ')
    {
        $page_cfg['rows'] = $rows;

        $pg   = new \ounun\page($this->db,$table,$url,$where,$where_bind,'count(*)',$page_cfg);
        $ps   = $pg->init($page,"",$page_max);
        // echo $this->db->sql()."<br />\n";
        $rs0  = $this->db->data_array("select {$fields} from {$table} {$where} {$order_by} limit {$pg->limit_start()},{$rows};",$where_bind);
        $rs   = [];
        foreach ($rs0 as $v){
            $v['exts'] = json_decode($v['exts'],true);
            $rs[]      = $v;
        }
        // echo $this->db->sql()."<br />\n";
        return [$rs,$ps];
    }


    /**
     * @param int $count
     * @param int $start
     * @param string $order
     * @param array $where
     * @return array
     */
    public function pics_lists_simple(int $count = 4, int $start = 0,string $order = ' ORDER BY `times` DESC ',string $where = "", array $bind = [])
    {
        $mod_id = \site_cfg::mod_pics;
        $rs0    = $this->db->data_array("SELECT * FROM `data` where `mod_id` = {$mod_id} {$where} {$order} LIMIT {$start} , {$count};",$bind);
        $rs     = [];
        foreach ($rs0 as $v){
            $v['exts'] = json_decode($v['exts'],true);
            $rs[]      = $v;
        }
        // echo $this->db->sql()."\n";
        return $rs;
    }

    /**
     * @param $pic_id
     * @return array
     */
    public function pics_details(int $data_id)
    {
        $rs         = $this->db->row('SELECT * FROM `data` WHERE `data_id` = ? LIMIT 0 , 1;',$data_id);
        $rs['exts'] = json_decode($rs['exts'],true);
        return $rs;
    }

    /**
     * @param int $data_id
     * @return array
     */
    public function pics_next(int $data_id)
    {
        $rs         = $this->db->row('SELECT * FROM `data` WHERE `data_id` > ? ORDER BY `data_id` ASC  LIMIT 0 , 1;',$data_id);
        $rs['exts'] = json_decode($rs['exts'],true);
        return $rs;
    }

    /**
     * @param int $data_id
     * @return array
     */
    public function pics_pre(int $data_id)
    {
        $rs         = $this->db->row('SELECT * FROM `data` WHERE `data_id` < ? ORDER BY `data_id` DESC LIMIT 0 , 1;',$data_id);
        $rs['exts'] = json_decode($rs['exts'],true);
        return $rs;
    }

    /**
     * @param int $count
     * @param int $start
     * @param string $order
     * @param string $where
     * @param array $bind
     * @return array
     */
    public function news_lists_simple(int $count = 4, int $start = 0,string $order = ' ORDER BY `times` DESC ',string $where = "", array $bind = [])
    {
        $mod_id = \site_cfg::mod_news;
        $rs0    = $this->db->data_array("SELECT * FROM `data` where `mod_id` = {$mod_id} {$where} {$order} LIMIT {$start} , {$count};",$bind);
        $rs     = [];
        foreach ($rs0 as $v){
            $v['exts'] = json_decode($v['exts'],true);
            $rs[]      = $v;
        }
        // echo $this->db->sql()."\n";
        return $rs;
    }

    /**
     * @param int $mod_id
     * @param string $key
     * @return array|mixed
     */
    public function site_config_1(int $mod_id = \site_cfg::mod_index,string $key = 'index')
    {
        $where  = $key?' and `key` = :key ':'';
        $bind   = ['key'=>$key];
        $rs     = $this->db->row("SELECT `id`,`value` FROM  `site_config` where `mod_id` = {$mod_id} {$where} LIMIT 0,1;",$bind);
        // echo $this->db->sql()."\n";
        $value  = [];
        if($rs && $rs['value']){
            $value = json_decode($rs['value'],true);
        }
        // echo $this->db->sql();
        return $value;
    }

    /**
     * 热门搜索 / 热门标签
     * @param int $count
     * @param int $tag_count
     * @return array
     */
    public function tags(int $count = 100,string $where = "  where `pinyin` != '' ")
    {
        $rs0 = $this->db->data_array("SELECT `tag`,`pinyin`,`tag_id`,`exts` FROM `tag` {$where} ORDER BY `official` ASC  LIMIT 0 , {$count};");
        $rs     = [];
        foreach ($rs0 as $v){
            $v['exts'] = json_decode($v['exts'],true);
            $rs[]      = $v;
        }
        // echo $this->db->sql()."\n";
        return $rs;
    }

    /**
     * @param $pinyin
     * @return array
     */
    public function tags_p2n($pinyin)
    {
        $rs = $this->db->row("SELECT `tag`,`pinyin`,`tag_id`,`exts` FROM `tag` where `pinyin` = :pinyin    LIMIT 0 , 1;",['pinyin'=>$pinyin]);
        if($rs && $rs['exts']){
            $rs['exts'] = json_decode($rs['exts'],true);
        }
        // echo $this->db->sql()."\n";
        return $rs;
    }

    /**
     * @param array $tag
     * @return array
     */
    public function tags_name(array $tag)
    {
        $rs = $this->db->data_array("SELECT `tag`,`pinyin`,`tag_id` FROM `tag` where `tag` in (?) ;",$tag);
        // echo $this->db->sql()."\n";
        return $rs;
    }
}
