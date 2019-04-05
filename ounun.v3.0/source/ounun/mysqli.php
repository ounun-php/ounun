<?php
/* <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
 * 文件:Mysqli.class.php
 * 用途:MYSQL類
 * 作者:(dreamxyp@gmail.com)[QQ:31996798]
 * 更新:2007.7.10
 * <meta http-equiv="Content-Type" content="text/html; charset=utf-8"> */

namespace ounun;


class mysqli
{
    /** @var string 当前数据库名 */
    protected static $_curr_db = '';
    /** @var string 当前数据库charset */
    protected static $_curr_charset = '';
    /** @var \mysqli_result */
    protected $_rs;
    /** @var \mysqli  connection */
    protected $_conn = null; //_connection
    /** @var string */
    protected $_sql = '';
    /** @var int */
    protected $_insert_id = 0;
    /** @var int */
    protected $_query_times = 0;
    /** @var int */
    protected $_query_affected = 0;
    /** @var string db charset */
    protected $_charset = 'utf8'; //,'utf8','gbk',latin1;

    /** @var string */
    protected $_database = '';
    protected $_username = '';
    protected $_password = '';
    protected $_host = '';
    protected $_post = 3306;

    /**
     * 创建MYSQL类
     * mysqli constructor.
     * @param array $cfg
     */
    public function __construct(array $cfg)
    {
        if ($cfg['charset']) {
            $this->_charset = $cfg['charset'];
        }
        $host = explode(':', $cfg['host']);
        $this->_post = (int)$host[1];
        $this->_host = $host[0];
        $this->_database = $cfg['database'];
        $this->_username = $cfg['username'];
        $this->_password = $cfg['password'];
    }

    /**
     * 激活当前连接
     * 主要是解决如果多个库在同一个MYSQL实例时会出现不能自动切换
     */
    public function active()
    {
        if (null == $this->_conn) {
            $this->_conn = new \mysqli($this->_host, $this->_username, $this->_password, $this->_database, $this->_post);
        }
        if ($this->_database && self::$_curr_db != $this->_database) {
            $this->_conn->select_db($this->_database);
            self::$_curr_db = $this->_database;
        }
        if ($this->_charset && self::$_curr_charset != $this->_charset) {
            $this->_conn->set_charset($this->_charset);
            self::$_curr_charset = $this->_charset;
        }
    }

    /**
     * 格式化sql语句
     *
     * @param string $sql
     * @param null|array $bind
     * @return string
     */
    protected function format(string $sql, $bind = null): string
    {
        if ($bind) {
            if (strpos($sql, '?') !== false) {
                return $this->quote_into($sql, $bind);
            } else {
                return $this->bind_value($sql, $bind);
            }
        } else {
            return $sql;
        }
    }

    /**
     * 格式化有数组的SQL语句
     *
     * @param  $sql
     * @param  $bind
     * @return string
     */
    protected function bind_value(string $sql, $bind): string
    {
        $rs = preg_split('/(\:[A-Za-z0-9_]+)\b/', $sql, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
        $rs2 = [];
        foreach ($rs as $v) {
            if ($v[0] == ':') {
                $rs2[] = $this->quote($bind[substr($v, 1)]);
            } else {
                $rs2[] = $v;
            }
        }
        return implode('', $rs2);
    }

    /**
     * 格式化问号(?)的SQL语句
     *
     * @param string $text
     * @param $value
     * @return string
     */
    protected function quote_into(string $text, $value): string
    {
        return str_replace('?', $this->quote($value), $text);
    }

    /**
     * mysql_real_escape_string
     *
     * @param  $value
     * @return string
     */
    public function quote($value)
    {
        if (is_array($value)) {
            $vals = [];
            foreach ($value as $val) {
                $vals[] = $this->quote($val);
            }
            return implode(', ', $vals);
        } else {
            return "'" . $this->_conn->real_escape_string($value) . "'";
        }
    }

    /**
     * 发送一条 MySQL 查询
     *
     * @param string $sql
     * @param null $bind
     * @return bool|\mysqli_result
     */
    public function conn(string $sql = '', $bind = null, bool $check_active = true)
    {
        if ($check_active) {
            $this->active();
        }
        if ($sql) {
            $this->_sql = $this->format($sql, $bind);
        }
        $this->_rs = $this->_conn->query($this->_sql);
        $this->_query_times++;
        return $this->_rs;
    }

    /**
     * 插入一條或多条記錄
     *
     * @param string $table 表名
     * @param array $bind 数据
     * @param string $param 可选参数  //[LOW_PRIORITY | DELAYED(仅适用于MyISAM, MEMORY和ARCHIVE表) | HIGH_PRIORITY] [IGNORE]
     * @param string $ext 扩展  //ON DUPLICATE KEY UPDATE col_name=expr, ...
     * @param array $bind2 数据
     * @return int $insert_id
     */
    public function insert(string $table, array $bind, string $params = '', string $ext = '', array $bind2 = []): int
    {
        $this->active();
        // Check for associative array
        if (array_keys($bind) !== range(0, count($bind) - 1)) {
            // Associative array
            $cols = array_keys($bind);
            $sql = "INSERT {$params} INTO {$table} " . '(`' . implode('`, `', $cols) . '`) ' . 'VALUES (:' . implode(', :', $cols) . ') ' . $this->format($ext, $bind2) . ';';

            $this->conn($sql, $bind, false);
        } else {
            // Indexed array
            $tmpArray = [];
            $cols = array_keys($bind[0]);
            foreach ($bind as $v) {
                $tmpArray[] = $this->format(' :' . implode(', :', $cols) . ' ', $v);
            }
            $sql = "INSERT {$params} INTO {$table} " . '(`' . implode('`, `', $cols) . '`) ' . 'VALUES (' . implode('),(', $tmpArray) . ') ' . $this->format($ext, $bind2) . ';';
            $this->conn($sql, null, false);
        }

        $this->_insert_id = $this->_conn->insert_id;     // 取得上一步 INSERT 操作产生的 ID
        $this->_query_affected = $this->_conn->affected_rows; // 取得前一次 MySQL 操作所影响的记录行数
        return $this->_insert_id;
    }

    /**
     * 插入一條或更新一条
     *
     * @param string $table 表名
     * @param array $primary 数据
     * @param array $bind 数据
     * @param string $operate
     * @return int insert_id
     */
    public function insert_update(string $table, array $primary, array $bind, string $operate = 'update'): int
    {
        $update = [];
        foreach ($bind as $col => $val) {
            if ($operate == 'add') {
                $update[] = "`$col` = `$col` + " . (float)$val;
            } elseif ($operate == 'cut') {
                $update[] = "`$col` = `$col` - " . (float)$val;
            } else {
                $update[] = "`$col` = :$col ";//.$this->quote($val);
            }
        }
        $primary = $primary + $bind;

        $cols = array_keys($primary);
        $sql = "INSERT  INTO {$table} " . '(`' . implode('`, `', $cols) . '`) ' . 'VALUES (:' . implode(', :', $cols) . ')  ON DUPLICATE KEY UPDATE ' . implode(' , ', $update) . ';';
        $this->conn($sql, $primary);

        $this->_insert_id = $this->_conn->insert_id; //取得上一步 INSERT 操作产生的 ID
        $this->_query_affected = $this->_conn->affected_rows; //取得前一次 MySQL 操作所影响的记录行数
        return $this->_insert_id;
    }

    /**
     * 快速地从一个或多个表中向一个表中插入多个行
     *
     * @param string $table 表名
     * @param string $sql INSERT ... SELECT语法
     * @param array $bind INSERT ... SELECT语法 中的$bind
     * @param string $param 可选参数  //[LOW_PRIORITY | DELAYED(仅适用于MyISAM, MEMORY和ARCHIVE表) | HIGH_PRIORITY] [IGNORE]
     * @return int insert_id
     */
    public function insert_import(string $table, array $sql, array $bind, string $param = ''): int
    {
        $this->conn("INSERT {$param} INTO {$table} {$sql} ", $bind);

        $this->_insert_id = $this->_conn->insert_id;     // 取得上一步 INSERT 操作产生的 ID
        $this->_query_affected = $this->_conn->affected_rows; // 取得前一次 MySQL 操作所影响的记录行数
        return $this->_insert_id;
    }

    /**
     * 替换(插入)一條或多条記錄
     *
     * @param string $table 表名
     * @param array $bind 数据  Array
     * @param string $param 可选参数  //[LOW_PRIORITY | DELAYED(仅适用于MyISAM, MEMORY和ARCHIVE表)]
     * @return int insert_id
     */
    public function replace(string $table, array $bind, string $param = ''): int
    {
        // Check for associative array
        if (array_keys($bind) !== range(0, count($bind) - 1)) {
            // Associative array
            $cols = array_keys($bind);
            $sql = "REPLACE {$param} INTO {$table} " . '(`' . implode('`, `', $cols) . '`) ' . 'VALUES (:' . implode(', :', $cols) . ') ;';
            $this->conn($sql, $bind);
        } else {
            // Indexed array
            $tmpArray = [];
            $cols = array_keys($bind[0]);
            foreach ($bind as $v) {
                $tmpArray[] = $this->format(' :' . implode(', :', $cols) . ' ', $v);
            }
            $sql = "REPLACE {$param} INTO {$table} " . '(`' . implode('`, `', $cols) . '`) ' . 'VALUES (' . implode('),(', $tmpArray) . ') ;';
            $this->conn($sql);
        }
        $this->_query_affected = $this->_conn->affected_rows; //取得前一次 MySQL 操作所影响的记录行数
        return $this->_query_affected;
    }

    /**
     * 快速地从一个或多个表中向一个表中替换(插入)多个行
     *
     * @param string $table 表名
     * @param string $sql
     * @param array $bind 数据  Array
     * @param string $param 可选参数  //[LOW_PRIORITY | DELAYED(仅适用于MyISAM, MEMORY和ARCHIVE表)]
     * @return int insert_id
     */
    public function replace_import(string $table, string $sql = '', $bind = null, string $param = ''): int
    {
        $this->conn("REPLACE {$param} INTO {$table} {$sql} ", $bind);

        $this->_query_affected = $this->_conn->affected_rows; //取得前一次 MySQL 操作所影响的记录行数
        return $this->_query_affected;
    }

    /**
     * 用新值更新原有表行中的各列
     *
     * @param string $table 表名
     * @param array $data 数据数组
     * @param string $where 条件
     * @param array $bind 条件数组
     * @param string $param 可选参数 [LOW_PRIORITY] [IGNORE]
     * @return int query_affected
     */
    public function update(string $table, array $data, string $where = '', $bind = null, string $param = '', int $limit = 0): int
    {
        $this->active();
        if ($where && $bind) {
            $where = $this->format($where, $bind);
        }
        $set = [];
        foreach ($data as $col => $value) {
            $set[] = "`$col` = " . $this->quote($value);
        }
        $sql = "UPDATE {$param} {$table} " . 'SET ' . implode(', ', $set) . (($where) ? " WHERE {$where}" : '') . ($limit ? ' LIMIT ' . $limit : '');
        $this->conn($sql, null, false);

        $this->_query_affected = $this->_conn->affected_rows; //取得前一次 MySQL 操作所影响的记录行数
        return $this->_query_affected;
    }

    /**
     * 數椐疊加
     *
     * @param string $table 表名
     * @param array $data 数据数组
     * @param string $where 条件
     * @param array $bind 条件数组
     * @param string $param 可选参数 [LOW_PRIORITY] [IGNORE]
     * @return int query_affected
     */
    public function add(string $table, array $data, string $where = '', $bind = null, string $param = ''): int
    {
        $this->active();
        if ($where && $bind) {
            $where = $this->format($where, $bind);
        }
        $set = [];
        foreach ($data as $col => $val) {
            $set[] = "`$col` = `$col` + " . (float)$val;
        }
        $sql = "UPDATE {$param} {$table} " . 'SET ' . implode(', ', $set) . (($where) ? " WHERE {$where}" : '');
        $this->conn($sql, null, false);

        $this->_query_affected = $this->_conn->affected_rows; //取得前一次 MySQL 操作所影响的记录行数
        return $this->_query_affected;
    }

    /**
     * 數椐递减
     *
     * @param string $table 表名
     * @param array $data 数据数组
     * @param string $where 条件
     * @param array $bind 条件数组
     * @param string $param 可选参数 [LOW_PRIORITY] [IGNORE]
     * @return int query_affected
     */
    public function cut(string $table, array $data, string $where = '', $bind = null, string $param = ''): int
    {
        $this->active();
        if ($where && $bind) {
            $where = $this->format($where, $bind);
        }
        $set = [];
        foreach ($data as $col => $val) {
            $set[] = "`$col` = `$col` - " . (float)$val;
        }
        $sql = "UPDATE {$param} {$table} " . 'SET ' . implode(', ', $set) . (($where) ? " WHERE {$where}" : '');
        $this->conn($sql, null, false);

        $this->_query_affected = $this->_conn->affected_rows; //取得前一次 MySQL 操作所影响的记录行数
        return $this->_query_affected;
    }

    /**
     * 删除记录
     *
     * @param string $table 表名
     * @param string $where 条件
     * @param array $bind 条件数组
     * @param string $param 可选参数 [LOW_PRIORITY] [QUICK] [IGNORE]
     * @return int query_affected
     */
    public function delete(string $table, string $where = '', $bind = null, string $param = ''): int
    {
        $sql = "DELETE {$param} FROM {$table} " . (($where) ? " WHERE $where" : '');
        $this->conn($sql, $bind);

        $this->_query_affected = $this->_conn->affected_rows; //取得前一次 MySQL 操作所影响的记录行数
        return $this->_query_affected;
    }

    /**
     * 得到數椐的行數
     *
     * @param string $sql
     * @param array $bind 条件数组
     * @return int
     */
    public function rows(string $sql = '', $bind = null): int
    {
        $sql && $this->conn($sql, $bind);
        if ($this->_rs) {
            return $this->_rs->num_rows;
        } else {
            return 0;
        }
    }

    /**
     * 得到一条數椐數組
     *
     * @param string $sql
     * @param array $bind 条件数组
     * @return array
     */
    public function row(string $sql = '', $bind = null): array
    {
        $sql && $this->conn($sql, $bind);
        if ($this->_rs) {
            $rs = $this->_rs->fetch_assoc();
            if ($rs) {
                return $rs;
            }
        }
        return [];
    }

    /**
     * 得到多条數椐數組的数组
     *
     * @param string $sql
     * @param array $bind 条件数组
     * @return array
     */
    public function data_array(string $sql = '', $bind = null): array
    {
        $sql && $this->conn($sql, $bind);
        $rs = [];
        if ($this->_rs) {
            while ($rss = $this->_rs->fetch_assoc()) {
                $rs[] = $rss;
            }
            $this->free();
        }
        return $rs;
    }

    /**
     * 从结果集中取得一行(指定行)作为关联数组
     *
     * @param string $sql
     * @param array $bind 条件数组
     * @param string $key_field 可选 指定行
     * @return array
     */
    public function fetch_assoc(string $sql = '', $bind = null, string $key_field = ''): array
    {
        $sql && $this->conn($sql, $bind);
        $rs = [];
        if ($key_field) {
            while ($rss = $this->_rs->fetch_assoc()) {
                $rs[$rss[$key_field]] = $rss;
            }
        } else {
            while ($rss = $this->_rs->fetch_assoc()) {
                $tmp = array_values($rss);
                $rs[$tmp[0]] = $rss;
            }
        }
        $this->free();
        return $rs;
    }

    /**
     * 回某個欄位元的內容是否重複
     *
     * @param string $table
     * @param string $field
     * @param string $value
     * @return boolean 有返回true 沒有為false
     */
    public function row_repeat(string $table, string $field, string $value)
    {
        if ($table && $field && $value) {
            $row = $this->row("select count(`{$field}`) as cc from {$table} where `{$field}` = ? ", $value);
            return $row['cc'] ? true : false;
        }
        return false;
    }

    /**
     * 返回最後一次插入的自增ID
     *
     * @return int
     */
    public function insert_id()
    {
        return $this->_insert_id;
    }

    /**
     * 返回查詢的次數
     *
     * @return int
     */
    public function query_times()
    {
        return $this->_query_times;
    }

    /**
     * 得到最后一次查询的sql
     *
     * @return string
     */
    public function sql(): string
    {
        return $this->_sql;
    }

    /**
     * 得到最后一次更改的行数
     *
     * @return int
     */
    public function affected(): int
    {
        return $this->_query_affected;
    }

    /** 清空内存 */
    public function free()
    {
        if ($this->_rs) {
            $this->_rs->free_result();
        }
    }

    /** 關閉打開的連接  */
    public function close()
    {
        if ($this->_conn) {
            $this->_conn->close();
        }
    }

    /** 關閉打開的連接   */
    public function is_connect(): bool
    {
        return $this->_conn ? true : false;
    }


    /**
     * Writes the log and returns the exception
     *
     * @param  string $message
     * @param  string $sql
     * @return string
     */
    private function exception_log($message = "", $sql = "")
    {
        $sql = $sql ? $sql : $this->_sql;

        $exception = "Unhandled Exception. <br />\r\n";
        $exception .= $message ? $message . "<br />\r\n" : '';
        $exception .= $sql ? $sql . "<br />\r\n" : '';

        return $exception;
    }
}
