<?php
namespace ounun;

/*###########################<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
文件:Mysql.class.php
用途:MYSQL類
作者:(dreamxyp@gmail.com)[QQ31996798]
更新:2007.7.10
#############################<meta http-equiv="Content-Type" content="text/html; charset=utf-8">*/
class mysql
{
    #class cache
    private $_rs;
    private $_conn; //_connection
    private $_sql;
    private $_insert_id;
    private $_query_times;
    private $_query_affected;
    #db charset
    private $_charset = 'utf8'; //,'utf8','gbk',latin1;
    private $_database;

    /**
     * 创建MYSQL类
     *
     * @param Array $config
     */
    public function __construct($config)
    {
        $config['charset'] && $this->_charset = $config['charset'];
        $this->_database = $config['database'];
        $this->_conn 	 = mysql_connect($config['host'], $config['username'], $config['password'], 0, MYSQL_CLIENT_IGNORE_SPACE);// or die("DateBase Err: " . mysql_errno() . ": " . mysql_error());
        mysql_select_db($this->_database, $this->_conn);
        mysql_set_charset($this->_charset, $this->_conn);
    }

    /**
     * 激活
     */
    public function active()
    {
    	mysql_select_db($this->_database,  $this->_conn);
    	mysql_set_charset($this->_charset, $this->_conn);
    }
    /**
     * 格式化sql语句
     *
     * @param string $sql
     * @param array $bind
     * @return string
     */
    private function _format($sql, $bind = null)
    {
        if(null !== $bind)
        {
            if(strpos($sql, '?') !== false)
            {
                return $this->_quote_into($sql, $bind);
            }
            else
          {
                return $this->_bind_value($sql, $bind);
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
    private function _bind_value($sql, $bind)
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
     * @param  $sql
     * @param  $bind
     * @return string
     */
    private function _quote_into($text, $value)
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
                $vals[] = $this->quote($val);
            return implode(', ', $vals);
        }
        else
       {
            return "'" . mysql_real_escape_string($value, $this->_conn) . "'";
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
			$this->_sql = $this->_format($sql, $bind);
		}
        //echo '<br />',$this->_sql,$this->_database;
        $this->_rs		= mysql_query($this->_sql, $this->_conn);
        $this->_query_times++;
        return $this->_rs;
    }

    /**
     * 插入一條或多条記錄
     *
     * @param  $table 表名  String
     * @param  $bind  数据  Array
     * @param  $param 可选参数  //[LOW_PRIORITY | DELAYED(仅适用于MyISAM, MEMORY和ARCHIVE表) | HIGH_PRIORITY] [IGNORE]
     * @param  $ext   扩展  //ON DUPLICATE KEY UPDATE col_name=expr, ...
     * @param  $bind2 $ext 数据  Array
     * @return insertId
     */
    public function insert($table, $bind, $param = '', $ext = '', $bind2 = null)
    {
        // Check for associative array
        if(array_keys($bind) !== range(0, count($bind) - 1))
        {
            // Associative array
            $cols 	= array_keys($bind);
            $sql 	= "INSERT {$param} INTO {$table} " . '(`' . implode('`, `', $cols) . '`) ' . 'VALUES (:' . implode(', :', $cols) . ') ' . $this->_format($ext, $bind2).';';
            
            $this->conn($sql, $bind);
        }
        else
       {
            // Indexed array
            $tmpArray 	= array();
            $cols 		= array_keys($bind[0]);
            foreach ($bind as $v)
            {
                $tmpArray[] = $this->_format(' :' . implode(', :', $cols) . ' ', $v);
            }
            $sql = "INSERT {$param} INTO {$table} " . '(`' . implode('`, `', $cols) . '`) ' . 'VALUES (' . implode('),(', $tmpArray) . ') ' . $this->_format($ext, $bind2).';';
            $this->conn($sql);
        }
        $this->_insert_id 	  = mysql_insert_id($this->_conn); //取得上一步 INSERT 操作产生的 ID
        $this->_query_affected = mysql_affected_rows($this->_conn); //取得前一次 MySQL 操作所影响的记录行数
        return $this->_insert_id;
    }
    /**
     * 插入一條或更新一条
     *
     * @param  $table   表名  String
     * @param  $primary 数据  Array
     * @param  $bind    数据  Array
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
        
        $this->_insert_id = mysql_insert_id($this->_conn); //取得上一步 INSERT 操作产生的 ID
        $this->_query_affected = mysql_affected_rows($this->_conn); //取得前一次 MySQL 操作所影响的记录行数
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
        $sql = "INSERT {$param} INTO {$table} " . $this->_format($sql, $bind);
        $this->conn($sql, $bind);
        $this->_insert_id = mysql_insert_id($this->_conn);
        $this->_query_affected = mysql_affected_rows($this->_conn);
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
                $tmpArray[] = $this->_format(' :' . implode(', :', $cols) . ' ', $v);
            }
            $sql = "REPLACE {$param} INTO {$table} " . '(`' . implode('`, `', $cols) . '`) ' . 'VALUES (' . implode('),(', $tmpArray) . ') ;';
            $this->conn($sql);
        }
        $this->_query_affected = mysql_affected_rows($this->_conn);
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
        $sql = "REPLACE {$param} INTO {$table} " . $this->_format($sql, $bind);
        $this->conn($sql, $bind);
        $this->_query_affected = mysql_affected_rows($this->_conn);
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
        $where && $where = $this->_format($where, $bind);
        $set = array();
        foreach ($data as $col=>$value)
        {
            $set[] = "`$col` = ".$this->quote($value);
        }
        $sql = "UPDATE {$param} {$table} " . 'SET ' . implode(', ', $set) . (($where)?" WHERE {$where}":'') . ($limit?' LIMIT ' . ((int)$limit):'');
        $this->conn($sql);
        $this->_query_affected = mysql_affected_rows($this->_conn);
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
        $where && $where = $this->_format($where, $bind);
        $set = array();
        foreach ($data as $col=>$val)
        {
            $set[] = "`$col` = `$col` + " . (float)$val;
        }
        $sql = "UPDATE {$param} {$table} " . 'SET ' . implode(', ', $set) . (($where)?" WHERE {$where}":'');
        $this->conn($sql, $bind);
        $this->_query_affected = mysql_affected_rows($this->_conn);
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
        $where && $where = $this->_format($where, $bind);
        $set = array();
        foreach ($data as $col=>$val)
        {
            $set[] = "`$col` = `$col` - " . (float)$val;
        }
        $sql = "UPDATE {$param} {$table} " . 'SET ' . implode(', ', $set) . (($where)?" WHERE {$where}":'');
        $this->conn($sql, $bind);
        $this->_query_affected = mysql_affected_rows($this->_conn);
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
        $this->_query_affected = mysql_affected_rows($this->_conn);
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
            return mysql_num_rows($this->_rs);
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
            return mysql_fetch_assoc($this->_rs);
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
        $rs	= array();
        $this->rows($sql, $bind);
        while (  false !== ($rss = $this->row())  )
        {
        	$rs[] = $rss;
        }
        $this->free();
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
        $rs = array();
        $this->conn($sql, $bind);
        if($keyField)
        {
        	while (false !== ($rss = $this->row()) )
            {
                $rs[$rss[$keyField]] = $rss;
            }
        }
        else
        {
            while (false !== ($rss = $this->row()) )
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
        if(is_resource($this->_rs))
        {
        	return mysql_free_result($this->_rs);
        }
        return false;
    }

    /**
     * 關閉打開的連接     *
     */
    public function close()
    {
        $this->_conn && mysql_close($this->_conn);
    }
    
    /**
     * 關閉打開的連接     *
     */
    public function isConnect()
    {
    	return $this->_conn ? true :false;
    }
}
