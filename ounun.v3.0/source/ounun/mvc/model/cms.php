<?php

namespace ounun\mvc\model;

abstract class cms
{
    /** @var self 实例 */
    protected static $instance;

    /**
     * @param \ounun\pdo $db
     * @return $this 返回数据库连接对像
     */
    public static function i(\ounun\pdo $db = null): self
    {
        if (empty(static::$instance)) {
            if (empty($db)) {
                $db = \v::db_v_get();
            }
            static::$instance = new static($db);
        }
        return static::$instance;
    }

    /** @var \ounun\pdo */
    public $db;

    /** @var string  */
    public $table = '';

    /**  @var array  */
    public $tags = [];

    /**
     * cms constructor.
     * @param \ounun\pdo $db
     */
    public function __construct(\ounun\pdo $db = null)
    {
        if ($db) {
            $this->db = $db;
        }
        static::$instance = $this;
    }

    /**
     * @param string $table
     * @param array $where
     * @param array $order
     * @param string $url
     * @param int $page
     * @param array $page_config
     * @param string $title
     * @param string $field
     * @param bool $end_index
     * @return array
     */
    public function lists(array $where, array $order, string $url, int $page, array $page_config, string $title = "", string $field = '*',string $table = '', bool $end_index = true)
    {
        if(empty($table)){
            $table = $this->table;
        }
        if(empty($table)){
            exit('数据表:$table无数据');
        }
        /** 分页 */
        $pg  = new \ounun\page\base_max( $this->db, $table, $url, $where, $page_config);
        $ps  = $pg->init($page, $title,$end_index);
        $db  = $this->db->table($table);
        if($field){
            $db->field($field);
        }
        if($where && $where['str']){
            $db->where($where['str'], $where['bind']);
        }
        if($order && is_array($order)){
            foreach ($order as $v){
                $db->order($v['field'], $v['order']);
            }
        }
        $datas = $db->limit($pg->limit_rows(), $pg->limit_start() )->column_all();

        $this->_lists_decode($datas);
        // echo $this->db->sql()."\n";
        return [$datas,$ps];
    }


    /**
     * @param string $table
     * @param int $count
     * @param int $start
     * @param array $order
     * @param array $where
     * @param string $addon_tag
     * @return array
     */
    public function lists_simple(int $count = 4, int $start = 0, array $order = [], array $where = [],string $fields = '*',string $table = '')
    {
        if(empty($table)){
            $table = $this->table;
        }
        if(empty($table)){
            exit('数据表:$table无数据');
        }
        /** 分页 */
        $db = $this->db->table($table)->field($fields);
        if($order && is_array($order)){
            foreach ($order as $v){
                $db->order($v['field'],$v['order']);
            }
        }
        if($where && is_array($where) && $where['str']){
            $db->where($where['str'],$where['bind']);
        }
        $rs = $db->limit($count,$start)->column_all();
        // echo $this->db->sql()."\n";
        // $rs = [];
        $this->_lists_decode($rs);
        // echo $this->db->sql()."\n";
        return $rs;
    }

    
    /**
     * json数据decode
     * @param array $rs
     * @param bool $is_multi
     * @return mixed
     */
    abstract protected function _lists_decode(array &$rs , bool $is_multi = true);
}
