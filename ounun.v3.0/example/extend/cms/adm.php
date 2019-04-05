<?php

namespace extend\cms;

class adm extends \ounun\mvc\model\cms
{
    /** 公告 */
    public function note()
    {
        return '当前无公告';
    }

    /**
     * @param string $table
     * @param string $where
     * @param $where_bind
     * @param string $order_by
     * @param int $rows
     * @param string $url
     * @param int $page
     * @param array $page_cfg
     * @param bool $page_max
     * @param string $fields
     * @return array
     */
    public function lists(string $table, string $where, $where_bind, string $order_by, int $rows, string $url, int $page, array $page_cfg, bool $page_max, string $fields = ' * ')
    {
        $page_cfg['rows'] = $rows;

        $pg = new \ounun\page($this->db, $table, $url, $where, $where_bind, 'count(*)', $page_cfg);
        $ps = $pg->init($page, "", $page_max);
        // echo $this->db->sql()."<br />\n";
        $rs0 = $this->db->data_array("select {$fields} from {$table} {$where} {$order_by} limit {$pg->limit_start()},{$rows};", $where_bind);
        $rs = [];
        foreach ($rs0 as $v) {
            $v['tag'] = json_decode_array($v['tag']);
            $v['centent'] = json_decode_array($v['centent']);
            $v['exts'] = json_decode_array($v['exts']);
            $rs[] = $v;
        }
        // echo $this->db->sql()."<br />\n";
        return [$rs, $ps];
    }


    /**
     * @param int $count
     * @param int $start
     * @param string $order
     * @param array $where
     * @return array
     */
    public function lists_simple(int $mod_id, int $count = 4, int $start = 0, string $order = ' ORDER BY `times` DESC ', string $where = "", array $bind = [])
    {
        $rs0 = $this->db->data_array("SELECT * FROM `data` where `mod_id` = {$mod_id} {$where} {$order} LIMIT {$start} , {$count};", $bind);
        // echo $this->db->sql()."\n";
        $rs = [];
        foreach ($rs0 as $v) {
            $v['tag'] = json_decode_array($v['tag']);
            $v['centent'] = json_decode_array($v['centent']);
            $v['exts'] = json_decode_array($v['exts']);
            $rs[] = $v;
        }
        // echo $this->db->sql()."\n";
        return $rs;
    }

    /**
     * @param $pic_id
     * @return array
     */
    public function details(int $data_id)
    {
        $rs = $this->db->row('SELECT * FROM `data` WHERE `data_id` = ? LIMIT 0 , 1;', $data_id);
        $rs['tag'] = json_decode_array($rs['tag']);
        $rs['centent'] = json_decode_array($rs['centent']);
        $rs['exts'] = json_decode_array($rs['exts']);
        return $rs;
    }

    /**
     * @param int $data_id
     * @return array
     */
    public function next(int $data_id)
    {
        $rs = $this->db->row('SELECT * FROM `data` WHERE `data_id` > ? ORDER BY `data_id` ASC  LIMIT 0 , 1;', $data_id);
        $rs['tag'] = json_decode_array($rs['tag']);
        $rs['centent'] = json_decode_array($rs['centent']);
        $rs['exts'] = json_decode_array($rs['exts']);
        return $rs;
    }

    /**
     * @param int $data_id
     * @return array
     */
    public function pre(int $data_id)
    {
        $rs = $this->db->row('SELECT * FROM `data` WHERE `data_id` < ? ORDER BY `data_id` DESC LIMIT 0 , 1;', $data_id);
        $rs['tag'] = json_decode_array($rs['tag']);
        $rs['centent'] = json_decode_array($rs['centent']);
        $rs['exts'] = json_decode_array($rs['exts']);
        return $rs;
    }


    /** tag */
    public function tag_index(int $mod_id, int $count, int $data_count = 5)
    {
        $rs = $this->db->data_array("SELECT `tag_id`,`count` FROM `tag_stat` WHERE mod_id = {$mod_id} AND `count` >= {$data_count} order by `count` desc LIMIT {$count};");
        // echo $this->db->sql()."<br />\n";
        // exit();
        $ids = [];
        foreach ($rs as $v) {
            $ids[] = (int)$v['tag_id'];
        }
        return $this->db->fetch_assoc("SELECT `tag_id`,`pinyin`,`tag` FROM `tag` WHERE `tag_id` IN (" . implode(',', $ids) . ");", null, 'tag_id');
    }

    /**
     * @param int $mod_id
     * @param array $tag_names
     * @return array
     */
    public function tag_name2id($v)
    {
        if ($v && $v['tag'] && is_array($v['tag'])) {
            $tag_names = $v['tag'];
            // $mod_id    = $v['mod_id'];
            $rs = $this->db->fetch_assoc("SELECT `tag_id`,`pinyin`,`tag` FROM `tag` WHERE `tag` IN (?);", $v['tag'], 'tag');
            if ($rs) {
                $tag = [];
                foreach ($rs as $v) {
                    $tag[] = "<a href=\"/tag/{$v['tag_id']}.html\" title=\"{$v['tag']}\" target=\"_blank\">{$v['tag']}</a>";
                }
                return $tag;
            }
        }
        return [];
    }
}
