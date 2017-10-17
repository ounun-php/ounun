<?php
namespace ounun;

/*###########################<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
文件:Mysqli.class.php
用途:MYSQL類
作者:(dreamxyp@gmail.com)[QQ31996798]
更新:2007.7.10
#############################<meta http-equiv="Content-Type" content="text/html; charset=utf-8">*/
class Mysqli
{
    #class cache
    /**
     * @var \mysqli_result
     */
    protected $_rs;
    /**
     * @var \mysqli
     */
    protected $_conn; //_connection
    protected $_sql;
    protected $_insert_id;
    protected $_query_times;
    protected $_query_affected;
    #db charset
    protected $_charset = 'utf8'; //,'utf8','gbk',latin1;
    protected $_database;

    /**
     * 创建MYSQL类
     * Mysqli constructor.
     * @param $cfg 配制文件
     */
    public function __construct($cfg)
    {
        if($cfg['charset'])
        {
            $this->_charset = $cfg['charset'];
        }
        $host            = explode(':',$cfg['host']);
        $post            = (int)$host[1];
        $host            = $host[0];
        $this->_database = $cfg['database'];
        $this->_conn 	 = new \mysqli($host,$cfg['username'],$cfg['password'],$this->_database,$post);
        $this->_conn->set_charset($this->_charset);
    }

    /**
     * 激活当前连接
     * 主要是解决如果多个库在同一个MYSQL实例时会出现不能自动切换
     */
    public function active()
    {
        $this->_conn->select_db($this->_database);
        $this->_conn->set_charset($this->_charset);
    }

    /**
     * 格式化sql语句
     *
     * @param string $sql
     * @param null|array $bind
     * @return string
     */
    protected function format(string $sql, $bind = null):string
    {
        if(null !== $bind)
        {
            if(strpos($sql, '?') !== false)
            {
                return $this->quoteInto($sql, $bind);
            }
            else
            {
                return $this->bindValue($sql, $bind);
            }
        }
        else
        {
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
    protected function bindValue($sql, $bind)
    {
        $rs  = preg_split('/(\:[A-Za-z0-9_]+)\b/', $sql, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
        $rs2 = array();
        foreach ($rs as $v)
        {
        	if($v[0] == ':')
        	{
        		$rs2[] = $this->quote($bind[substr($v, 1)]);
        	}
        	else 
        	{
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
    protected function quoteInto(string $text, $value):string
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
        if(is_array($value))
        {
            $vals = array();
            foreach ($value as $val)
            {
                $vals[] = $this->quote($val);
            }
            return implode(', ', $vals);
        }
        else
        {
            return "'" . $this->_conn->real_escape_string($value) . "'";
        }
    }

    /**
     * 发送一条 MySQL 查询
     *
     * @param  $sql
     * @param  $bind
     * @return resource
     */
    public function conn($sql, $bind = null)
    {
        if($sql)
        {
			$this->_sql = $this->format($sql, $bind);
		}
        $this->_rs		= $this->_conn->query($this->_sql);
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
    public function insert(string $table, array $bind, string $params = '', string $ext = '', array $bind2 = [])
    {
        // Check for associative array
        if(array_keys($bind) !== range(0, count($bind) - 1))
        {
            // Associative array
            $cols 	= array_keys($bind);
            $sql 	= "INSERT {$params} INTO {$table} " . '(`' . implode('`, `', $cols) . '`) ' . 'VALUES (:' . implode(', :', $cols) . ') ' . $this->format($ext, $bind2).';';

            $this->conn($sql, $bind);
        }
        else
        {
            // Indexed array
            $tmpArray 	= array();
            $cols 		= array_keys($bind[0]);
            foreach ($bind as $v)
            {
                $tmpArray[] = $this->format(' :' . implode(', :', $cols) . ' ', $v);
            }
            $sql = "INSERT {$params} INTO {$table} " . '(`' . implode('`, `', $cols) . '`) ' . 'VALUES (' . implode('),(', $tmpArray) . ') ' . $this->format($ext, $bind2).';';
            $this->conn($sql);
        }
        //$this->_insert_id 	 = mysqli_insert_id($this->_conn); //取得上一步 INSERT 操作产生的 ID
        //$this->_query_affected = mysqli_affected_rows($this->_conn); //取得前一次 MySQL 操作所影响的记录行数
        $this->_insert_id 	     = $this->_conn->insert_id; //取得上一步 INSERT 操作产生的 ID
        $this->_query_affected   = $this->_conn->affected_rows; //取得前一次 MySQL 操作所影响的记录行数
        return $this->_insert_id;
    }
    /**
     * 插入一條或更新一条
     *
     * @param  表名 $table    String
     * @param  数据 $primary  Array
     * @param  数据 $bind     Array
     * @return queryAffected
     */
    public function insertUpdate($table, $primary, $bind,$operate='update')
    {
        $update= array();
        foreach ($bind as $col=>$val)
        {
        	if($operate =='add')
        	{
            	$update[] = "`$col` = `$col` + ". (float)$val;
        	}
        	elseif($operate =='cut')
        	{
        		$update[] = "`$col` = `$col` - ". (float)$val;
        	}
        	else 
        	{
        		$update[] = "`$col` = :$col ";//.$this->quote($val);
        	}
        }    	
        $primary = $primary+$bind;
        
        $cols = array_keys($primary);
        $sql  = "INSERT  INTO {$table} " . '(`' . implode('`, `', $cols) . '`) ' . 'VALUES (:' . implode(', :', $cols) . ')  ON DUPLICATE KEY UPDATE '.implode(' , ',$update).';';
        $this->conn($sql,$primary);
        
        //$this->_insert_id = mysqli_insert_id($this->_conn); //取得上一步 INSERT 操作产生的 ID
        //$this->_query_affected = mysqli_affected_rows($this->_conn); //取得前一次 MySQL 操作所影响的记录行数
        $this->_insert_id 	   = $this->_conn->insert_id; //取得上一步 INSERT 操作产生的 ID
        $this->_query_affected = $this->_conn->affected_rows; //取得前一次 MySQL 操作所影响的记录行数
        return $this->_insert_id;
    }
    /**
     * 快速地从一个或多个表中向一个表中插入多个行
     *
     * @param string $table 表名
     * @param string $sql   INSERT ... SELECT语法
     * @param array  $bind  INSERT ... SELECT语法 中的$bind
     * @param string $param 可选参数  //[LOW_PRIORITY | DELAYED(仅适用于MyISAM, MEMORY和ARCHIVE表) | HIGH_PRIORITY] [IGNORE]
     * @return insertId
     */
    public function insertImport($table, $sql, $bind, $param = '')
    {
        $sql = "INSERT {$param} INTO {$table} " . $this->format($sql, $bind);
        $this->conn($sql, $bind);
       // $this->_insert_id = mysqli_insert_id($this->_conn);
       // $this->_query_affected = mysqli_affected_rows($this->_conn);
        $this->_insert_id 	   = $this->_conn->insert_id; //取得上一步 INSERT 操作产生的 ID
        $this->_query_affected = $this->_conn->affected_rows; //取得前一次 MySQL 操作所影响的记录行数
        return $this->_insert_id;
    }

    /**
     * 替换(插入)一條或多条記錄
     *
     * @param string $table   表名
     * @param array  $bind    数据  Array
     * @param string $param   可选参数  //[LOW_PRIORITY | DELAYED(仅适用于MyISAM, MEMORY和ARCHIVE表)]
     * @return insertId
     */
    public function replace($table, $bind, $param = '')
    {
        // Check for associative array
        if(array_keys($bind) !== range(0, count($bind) - 1))
        {
            // Associative array
            $cols = array_keys($bind);
            $sql = "REPLACE {$param} INTO {$table} " . '(`' . implode('`, `', $cols) . '`) ' . 'VALUES (:' . implode(', :', $cols) . ') ;';
            $this->conn($sql, $bind);
        }
        else
       {
            // Indexed array
            $tmpArray = array();
            $cols 	  = array_keys($bind[0]);
            foreach ($bind as $v)
            {
                $tmpArray[] = $this->format(' :' . implode(', :', $cols) . ' ', $v);
            }
            $sql = "REPLACE {$param} INTO {$table} " . '(`' . implode('`, `', $cols) . '`) ' . 'VALUES (' . implode('),(', $tmpArray) . ') ;';
            $this->conn($sql);
        }
        //$this->_query_affected = mysqli_affected_rows($this->_conn);
        $this->_query_affected = $this->_conn->affected_rows; //取得前一次 MySQL 操作所影响的记录行数
        return $this->_query_affected;
    }

    /**
     * 快速地从一个或多个表中向一个表中替换(插入)多个行
     *
     * @param string $table 表名
     * @param string $sql
     * @param array  $bind  数据  Array
     * @param string $param 可选参数  //[LOW_PRIORITY | DELAYED(仅适用于MyISAM, MEMORY和ARCHIVE表)]
     * @return insertId
     */
    public function replaceImport($table, $sql, $bind, $param = '')
    {
        $sql = "REPLACE {$param} INTO {$table} " . $this->format($sql, $bind);
        $this->conn($sql, $bind);
        //$this->_query_affected = mysqli_affected_rows($this->_conn);
        $this->_query_affected = $this->_conn->affected_rows; //取得前一次 MySQL 操作所影响的记录行数
        return $this->_query_affected;
    }

    /**
     * 用新值更新原有表行中的各列
     *
     * @param string $table 表名
     * @param array  $data  数据数组
     * @param string $where 条件
     * @param array  $bind  条件数组
     * @param string $param 可选参数 [LOW_PRIORITY] [IGNORE]
     * @return queryAffected
     */
    public function update($table, $data, $where = null, $bind = null, $param = '', $limit = 0)
    {
        $where && $where = $this->format($where, $bind);
        $set = array();
        foreach ($data as $col=>$value)
        {
            $set[] = "`$col` = ".$this->quote($value);
        }
        $sql = "UPDATE {$param} {$table} " . 'SET ' . implode(', ', $set) . (($where)?" WHERE {$where}":'') . ($limit?' LIMIT ' . ((int)$limit):'');
        $this->conn($sql);
        //$this->_query_affected = mysqli_affected_rows($this->_conn);
        $this->_query_affected = $this->_conn->affected_rows; //取得前一次 MySQL 操作所影响的记录行数
        return $this->_query_affected;
    }

    /**
     * 數椐疊加
     *
     * @param string $table  表名
     * @param array  $data   数据数组
     * @param string $where  条件
     * @param array  $bind   条件数组
     * @param string $param  可选参数 [LOW_PRIORITY] [IGNORE]
     * @return queryAffected
     */
    public function add($table, $data, $where = null, $bind = null, $param = '')
    {
        $where && $where = $this->format($where, $bind);
        $set = array();
        foreach ($data as $col=>$val)
        {
            $set[] = "`$col` = `$col` + " . (float)$val;
        }
        $sql = "UPDATE {$param} {$table} " . 'SET ' . implode(', ', $set) . (($where)?" WHERE {$where}":'');
        $this->conn($sql, $bind);
        //$this->_query_affected = mysqli_affected_rows($this->_conn);
        $this->_query_affected = $this->_conn->affected_rows; //取得前一次 MySQL 操作所影响的记录行数
        return $this->_query_affected;
    }
    /**
     * 數椐递减
     *
     * @param string $table 表名
     * @param array $data   数据数组
     * @param string $where 条件
     * @param array $bind   条件数组
     * @param string $param 可选参数 [LOW_PRIORITY] [IGNORE]
     * @return queryAffected
     */
    public function cut($table, $data, $where = null, $bind = null, $param = '')
    {
        $where && $where = $this->format($where, $bind);
        $set = array();
        foreach ($data as $col=>$val)
        {
            $set[] = "`$col` = `$col` - " . (float)$val;
        }
        $sql = "UPDATE {$param} {$table} " . 'SET ' . implode(', ', $set) . (($where)?" WHERE {$where}":'');
        $this->conn($sql, $bind);
        //$this->_query_affected = mysqli_affected_rows($this->_conn);
        $this->_query_affected = $this->_conn->affected_rows; //取得前一次 MySQL 操作所影响的记录行数
        return $this->_query_affected;
    }
    /**
     * 删除记录
     *
     * @param string $table 表名
     * @param string $where 条件
     * @param array  $bind  条件数组
     * @param string $param 可选参数 [LOW_PRIORITY] [QUICK] [IGNORE]
     * @return queryAffected
     */
    public function delete($table, $where = null, $bind = null, $param = '')
    {
        $sql = "DELETE {$param} FROM {$table} " . (($where)?" WHERE $where":'');
        $this->conn($sql, $bind);
        //$this->_query_affected = mysqli_affected_rows($this->_conn);
        $this->_query_affected = $this->_conn->affected_rows; //取得前一次 MySQL 操作所影响的记录行数
        return $this->_query_affected;
    }

    /**
     * 得到數椐的行數
     *
     * @param string $sql
     * @param array  $bind 条件数组
     * @return int
     */
    public function rows($sql = null, $bind = null)
    {
        $sql && $this->conn($sql, $bind);
        if($this->_rs)
        {
            // return mysqli_num_rows($this->_rs);
            return $this->_rs->num_rows;
        }
        else
        {
            return 0;
        }
    }

    /**
     * 得到一条數椐數組
     *
     * @param string $sql
     * @param array  $bind 条件数组
     * @return array
     */
    public function row($sql = null, $bind = null)
    {
        $sql && $this->conn($sql, $bind);
        if($this->_rs)
        {
            return $this->_rs->fetch_assoc();
        }
        else
        {
        	return false;
        }
    }

    /**
     * 得到多条數椐數組的数组
     *
     * @param string $sql
     * @param array $bind 条件数组
     * @return array
     */
    public function dataArray($sql = null, $bind = null)
    {
        $sql && $this->conn($sql, $bind);
        $rs	= [];
        if($this->_rs)
        {
            while ( $rss = $this->_rs->fetch_assoc()  )
            {
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
     * @param array  $bind  条件数组
     * @param string $keyField 可选 指定行
     * @return array
     */
    public function fetchAssoc($sql = null, $bind = null, $keyField = null)
    {
        $sql && $this->conn($sql, $bind);
        $rs = array();
        if($keyField)
        {
        	while ($rss = $this->_rs->fetch_assoc() )
            {
                $rs[$rss[$keyField]] = $rss;
            }
        }
        else
        {
            while ($rss = $this->_rs->fetch_assoc() )
            {
                $tmp 				 = array_values($rss);
                $rs[$tmp[0]] 		 = $rss;
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
    public function rowRepeat($table, $field, $value)
    {
        if($table && $field && $value)
        {
            $row = $this->row("select count(`{$field}`) as cc from {$table} where `{$field}` = ? ", $value);
            // debug('rowRepeat|$row[\'cc\']:', $row['cc']);
            return $row['cc']?true:false;
        }
        return false;
    }

    /**
     * 返回最後一次插入的自增ID
     *
     * @return int
     */
    public function insertID()
    {
        return $this->_insert_id;
    }

    /**
     * 返回查詢的次數
     *
     * @return int
     */
    public function queryTimes()
    {
        return $this->_query_times;
    }

    /**
     * 得到最后一次查询的sql
     *
     * @return string
     */
    public function getSql()
    {
        return $this->_sql;
    }

    /**
     * 得到最后一次更改的行数
     *
     * @return int
     */
    public function affected()
    {
        return $this->_query_affected;
    }

    /**
     * 清空内存
     *
     * @return boolean
     */
    public function free()
    {
        if($this->_rs)
        {
            return $this->_rs->free_result();
        }
        return false;
    }

    /**
     * 關閉打開的連接     *
     */
    public function close()
    {
        if($this->_conn)
        {
            $this->_conn->close();
        }
    }
    
    /**
     * 關閉打開的連接     *
     */
    public function isConnect()
    {
    	return $this->_conn ? true :false;
    }
}
