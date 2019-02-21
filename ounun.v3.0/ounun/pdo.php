<?php

namespace ounun;


class pdo
{
    /** @var string 倒序大在前 - 排序 */
    const Order_Desc = 'desc';
    /** @var string 倒序大在前 - 排序 */
    const Order_Asc  = 'asc';

    //（INSERT、REPLACE、UPDATE、DELETE）
    /** @var string 低优先级 */
    const Option_Low_Priority   = 'LOW_PRIORITY';
    /** @var string 高优先级 */
    const Option_High_Priority  = 'HIGH_PRIORITY';
    /** @var string 延时 (仅适用于MyISAM, MEMORY和ARCHIVE表) */
    const Option_Delayed        = 'DELAYED';
    /** @var string 快的 */
    const Option_Quick          = 'QUICK';
    /** @var string 出错时忽视 */
    const Option_Ignore         = 'IGNORE';

    /** @var string utf8 */
    const Charset_Utf8          = 'utf8';
    /** @var string utf8mb4 */
    const Charset_Utf8mb4       = 'utf8mb4';
    /** @var string gbk */
    const Charset_Gbk           = 'gbk';
    /** @var string latin1 */
    const Charset_Latin1        = 'latin1';

    /** @var string Mysql */
    const Driver_Mysql          = 'mysql';

    /** @var string 更新操作Update */
    const Update_Update        = 'update';
    /** @var string 更新操作Cut */
    const Update_Cut           = 'cut';
    /** @var string 更新操作Add */
    const Update_Add           = 'add';

    /** @var \PDO  pdo */
    protected $_pdo            = null; // pdo
    /** @var \PDOStatement  stmt */
    protected $_stmt           = null; // stmt

    /** @var string */
    protected $_last_sql       = '';
    /** @var int */
    protected $_query_times    = 0;

    /** @var string 数据库名称 */
    protected $_database       = '';
    /** @var string 用户名 */
    protected $_username       = '';
    /** @var string 用户密码 */
    protected $_password       = '';
    /** @var string 数据库主机名称 */
    protected $_host           = '';
    /** @var string 数据库主机端口 */
    protected $_post           = 3306;
    /** @var string 数据库charset */
    protected $_charset        = 'utf8'; //'utf8mb4','utf8','gbk',latin1;
    /** @var string pdo驱动默认为mysql */
    protected $_driver         = 'mysql';

    /** @var string 当前table */
    protected $_table  = '';
    /** @var string 参数 */
    protected $_option = '';
    /** @var array 字段 */
    protected $_fields = [];
    /** @var array 排序字段 */
    protected $_order  = [];
    /** @var array 分组字段 */
    protected $_group  = [];
    /** @var string limit条件  */
    protected $_limit  = '';
    /** @var string 条件 */
    protected $_where  = '';
    /** @var array 条件参数 */
    protected $_where_param = [];
    /** @var array 插入时已存在数据 更新内容 */
    protected $_duplicate   = [];
    /** @var string 关联表 */
    protected $_inner_join  = '';

    /** @var bool 多条 */
    protected $_is_multiple = false;
    /** @var bool 是否替换插入 */
    protected $_is_replace  = false;

    /**
     * 创建MYSQL类
     * mysqli constructor.
     * @param array $config
     * @return $this
     */
    public function __construct(array $config)
    {
        $host            = explode(':',$config['host']);
        $this->_post     = (int)$host[1];
        $this->_host     =      $host[0];
        $this->_database = $config['database'];
        $this->_username = $config['username'];
        $this->_password = $config['password'];

        if($config['charset']){
            $this->_charset = $config['charset'];
        }
        if($config['driver']){
            $this->_driver  = $config['driver'];
        }
        return $this;
    }

    /**
     * @param string $table 表名
     * @return $this
     */
    public function table(string $table = ''):pdo
    {
        $this->_table = $table;
        return $this;
    }

    /**
     * 发送一条MySQL查询
     * @param string $sql
     * @param bool $check_active
     * @return $this
     */
    public function query(string $sql = '',bool $check_active = true)
    {
        if($check_active) {
            $this->active();
        }
        if($sql){
            $this->_last_sql  = $sql;
        }
        $this->_stmt = $this->_pdo->query($this->_last_sql);
        $this->_query_times++;
        return $this;
    }

    /**
     * 激活当前连接
     *   主要是解决如果多个库在同一个MYSQL实例时会出现不能自动切换
     */
    public function active()
    {
        if(null == $this->_pdo){
            $dsn         = "{$this->_driver}:host={$this->_host};port={$this->_post};dbname={$this->_database};charset={$this->_charset}";
            $options     = [];
            if(self::Driver_Mysql == $this->_driver) {
                $options = [
                    \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
                    //\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES '{$this->_charset}'"
                ];
            }
            $this->_pdo  = new \PDO($dsn,$this->_username,$this->_password,$options);
        }
    }

    /**
     * 替换(插入)一條或多条記錄
     * @param array $data 数据
     * @return int
     */
    public function insert(array $data):int
    {
        $ir = $this->_is_replace?'REPLACE':'INSERT';
        if(!$this->_is_multiple){
            $data  = [$data];
        }
        $duplicate = '';
        if($this->_duplicate) {
            $update = [];
            foreach ($this->_duplicate as $col => $op) {
                if($op == self::Update_Add) {
                    $update[] = "`$col` = `$col` + :$col ";
                } elseif($op == self::Update_Cut ) {
                    $update[] = "`$col` = `$col` - :$col ";
                } else {
                    $update[] = "`$col` = :$col ";//.$this->quote($val);
                }
            }
            $duplicate = 'ON DUPLICATE KEY UPDATE '.implode(' , ',$update);
        }
        $cols 	         = array_keys($data[0]);
        $this->_last_sql = "{$ir} {$this->_option} INTO {$this->_table} " . '(`' . implode('`, `', $cols) . '`) ' . 'VALUES (:' . implode(', :', $cols) . ') '.$duplicate.';';
        $this->_stmt     = $this->_pdo->prepare($this->_last_sql);
        foreach ($data as $data2){
            $param = [];
            foreach ($data2 as $k3=>$v3){
                $param[':'.$k3] = $v3;
            }
            $this->_stmt->execute($param);
        }
        return $this->_pdo->lastInsertId();
    }

    /**
     * 用新值更新原有表行中的各列
     * @param array $data 数据
     * @return int
     */
    public function update(array $data, array $operate = [],int $limit = 1)
    {
        $this->limit($limit);
        $update = [];
        foreach ($data as $col=>$val) {
            if($operate[$col] =='add') {
                $update[] = "`$col` = `$col` + ". (float)$val;
            }elseif($operate[$col] =='cut') {
                $update[] = "`$col` = `$col` - ". (float)$val;
            }else{
                $this->_where_param[':'.$col] = $val;
                $update[] = "`$col` = :$col ";//.$this->quote($val);
            }
        }
        $this->query("UPDATE {$this->_option} {$this->_table} SET ".implode(', ',$update).' '.$this->_where.' '.$this->_limit.' ;');
        if($this->_where_param) {
            $this->_stmt->execute($this->_where_param);
        }else{
            $this->_stmt->execute();
        }
        return $this->_stmt->rowCount();
    }

    /**
     * @return int
     */
    public function column_count()
    {
        $group = $this->_get_group();
        $this->query('SELECT '.implode(',',$this->_fields).' FROM '.$this->_table.' '.$this->_inner_join.' '.$this->_where.' '.$group.' ;');
        if($this->_where) {
            $this->_stmt->execute($this->_where_param);
        }else{
            $this->_stmt->execute();
        }
        return $this->_stmt->columnCount();
    }

    /**
     * @return array 得到一条数据数组
     */
    public function column_one()
    {
        $order = $this->_get_order();
        $group = $this->_get_group();
        $this->query('SELECT '.implode(',',$this->_fields).' FROM '.$this->_table.' '.$this->_inner_join.' '.$this->_where.' '.$group.' '.$order.' '.$this->_limit.';');
        if($this->_where) {
            $this->_stmt->execute($this->_where_param);
        }else{
            $this->_stmt->execute();
        }
        return $this->_stmt->fetch();
    }

    /**
     * @return array 得到多条數椐數組的数组
     */
    public function column_all()
    {
        $order = $this->_get_order();
        $group = $this->_get_group();
        $this->query('SELECT '.implode(',',$this->_fields).' FROM '.$this->_table.' '.$this->_inner_join.' '.$this->_where.' '.$group.' '.$order.' '.$this->_limit.';');
        if($this->_where) {
            $this->_stmt->execute($this->_where_param);
        }else{
            $this->_stmt->execute();
        }
        return $this->_stmt->fetchAll();
    }

    /**
     * 删除
     * @param int $limit 删除limit默认为1
     * @return int
     */
    public function delete(int $limit = 1):int
    {
        $this->limit($limit)->query("DELETE {$this->_option} FROM {$this->_table} {$this->_where} {$this->_limit};");
        if($this->_where) {
            $this->_stmt->execute($this->_where_param);
        }else{
            $this->_stmt->execute();
        }
        return $this->_stmt->rowCount(); //取得前一次 MySQL 操作所影响的记录行数
    }

    /**
     * 设定插入数据为替换
     * @param bool $replace
     * @return $this;
     */
    public function replace(bool $replace = false)
    {
        $this->_is_replace = $replace;
        return $this;
    }

    /**
     * 多条数据 true:多条数据 false:单条数据
     * @param bool $multiple
     * @return $this;
     */
    public function multiple(bool $multiple = false)
    {
        $this->_is_multiple = $multiple;
        return $this;
    }

    /**
     * 参数
     * @param string $option
     * @return $this
     */
    public function option(string $option = '')
    {
        $this->_option = $option;
        return $this;
    }

    /**
     * 插入时已存在数据
     * @param array $data 更新内容
     * @return $this
     */
    public function duplicate(array $data,array $operate = [])
    {
        $this->_duplicate = [$data,$operate];
        return $this;
    }

    /**
     * 指定查询数量
     * @param int $length  查询数量
     * @param int $offset  起始位置
     * @return $this
     */
    public function limit(int $length,int $offset = 0)
    {
        if(0 == $offset){
            $this->_limit   = "LIMIT {$length}";
        }else{
            $this->_limit   = "LIMIT {$offset},{$length}";
        }
        return $this;
    }

    /**
     * @param string $field
     * @return $this
     */
    public function field($field = '*')
    {
        $this->_fields[] = $field;
        return $this;
    }

    public function inner_join(string $inner_join,string $on)
    {
        $this->_inner_join = 'INNER JOIN '.$inner_join.' ON '.$on;
    }

    /**
     * 条件
     * @param string $where  条件
     * @param array  $param  条件参数
     * @return $this
     */
    public function where(string $where = '',array $param = [],array $types = [])
    {
        if($where) {
            $this->_where       = "WHERE {$where}";
            $this->_where_param = [];
            if($param) {
                foreach ($param as $k => $v){
                    $this->_where_param[':'.$k] = $v;
                }
                $this->_where_param = $param;
            }
        }
        return $this;
    }

    /**
     * 指定排序
     * @param string $field 排序字段
     * @param string $order 排序
     * @return $this
     */
    public function order(string $field,string $order = self::Order_Desc)
    {
        $this->_order[] = [ 'field' => $field, 'order' => $order ];
        return $this;
    }

    /**
     * 聚合分组
     * @param string $field
     * @return $this
     */
    public function group(string $field)
    {
        $this->_group[] = $field;
        return $this;
    }

    /**
     * COUNT查询
     * @param string $field 字段名
     * @param string $alias SUM查询别名
     * @return $this
     */
    public function count(string $field = '*',string $alias = '`count`')
    {
        return $this->field("COUNT({$field}) AS {$alias}");
    }

    /**
     * SUM查询
     * @param string $field 字段名
     * @param string $alias SUM查询别名
     * @return $this
     */
    public function sum(string $field,string $alias = '`sum`')
    {
        return $this->field("SUM({$field}) AS {$alias}");
    }

    /**
     * MIN查询
     * @param string $field 字段名
     * @param string $alias MIN查询别名
     * @return $this
     */
    public function min(string $field,string $alias = '`min`')
    {
        return $this->field("MIN({$field}) AS {$alias}");
    }

    /**
     * MAX查询
     * @param string $field 字段名
     * @param string $alias MAX查询别名
     * @return $this
     */
    public function max(string $field,string $alias = '`max`')
    {
        return $this->field("MAX({$field}) AS {$alias}");
    }

    /**
     * AVG查询
     * @param string $field 字段名
     * @param string $alias AVG查询别名
     * @return $this
     */
    public function avg(string $field,string $alias = '`avg`')
    {
        return $this->field("AVG({$field}) AS {$alias}");
    }

    /**
     * 返回查询次数
     * @return int
     */
    public function query_times():int
    {
        return $this->_query_times;
    }

    /**
     * 最后一次插入的自增ID
     * @return int
     */
    public function insert_id():int
    {
        return $this->_pdo->lastInsertId();
    }

    /**
     * 最后一次更新影响的行数
     * @return int
     */
    public function affected():int
    {
        return $this->_stmt->columnCount();
    }

    /**
     * 得到最后一次查询的sql
     * @return string
     */
    public function last_sql():string
    {
        return $this->_last_sql;
    }

    /**
     * 返回PDO
     * @return \PDO 返回PDO
     */
    public function pdo():\PDO
    {
        return $this->_pdo;
    }

    /**
     * 是否连接成功
     * @return bool
     */
    public function is_connect():bool
    {
        return $this->_pdo ? true :false;
    }

    /** order */
    protected function _get_order()
    {
        $rs = '';
        if($this->_order && is_array($this->_order)) {
            $rs2  = [];
            foreach ($this->_order as $v){
                $rs2[] = '`'.$v['field'].'` '.$v['order'];
            }
            $rs  = ' order by '.implode(',',$rs2);
        }
        return $rs;
    }

    /** group */
    protected function _get_group()
    {
        $rs = '';
        if($this->_group && is_array($this->_group)) {
            $rs  = ' group by '.implode(',',$this->_group);
        }
        return $rs;
    }

    /**
     * 捡查指定字段数据是否存在
     * @param string $field 字段
     * @param string $value 值
     * @return bool
     */
    public function is_repeat(string $field,string $value):bool
    {
        if($field){
            $rs = $this->where(' `{$field}` = s:field ',['field'=>$value])->count()->column_one();
            if($rs && $rs['count']){
                return true;
            }
        }
        return false;
    }

    /**
     * 当类需要被删除或者销毁这个类的时候自动加载__destruct这个方法
     */
    public function __destruct()
    {
        $this->_stmt = null;
        $this->_pdo  = null;
    }
}