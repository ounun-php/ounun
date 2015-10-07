<?php
namespace sdk\oauth;
/**
 * @brief 设置session配置 
 */

class OSession
{
    /**
     * MYSQL handle
     * @var \ounun\Mysql
     */
    private $db_handle;

    /**
     * 需要第三方指定数据表
     * @var int|string
     */
    private $db_table  = "`yx_oauth_session`";

    /**
     * session-lifetime 默认时间
     * @var int
     */
    private $life_time = 7200;

    /**
     * @param \ounun\Mysql $db
     * @param string $db_table
     * @param int $life_time
     */
    public function __construct(\ounun\Mysql $db, string $db_table = "yx_oauth_session", int $life_time=7200)
    {
        // $db
        $this->db_handle = $db;
        // $db_table
        $this->db_table  = $db_table;
        // $life_time
        $this->life_time = $life_time;
        return true;
    }

    public function close()
    {
        $this->gc();
        // close database-connection
        return true;
    }

    public function read($session_type,$session_id)
    {
        $bind = array(
            'session_type'      => $session_type,
            'session_id'        => $session_id,
            'session_expires'   => time()
        );
        // fetch session-data
        $rs   = $this->db_handle->row("SELECT `session_data` AS `d` FROM {$this->db_table} WHERE `session_type` = :session_type and `session_id` = :session_id  AND `session_expires` > :session_expires limit 1; ",$bind);
        // return data or an empty string at failure
        if($rs && $rs['d'])
        {
            return $rs['d'];
        }else
        {
            return "";
        }
    }

    public function write($session_type,$session_id, $session_data)
    {
        // new session-expire-time
        $bind    = array(
            'session_type'      => $session_type,
            'session_id'        => $session_id,
            'session_expires'   => time() + $this->life_time,
            'session_data'      => $session_data
        );
        return $this->db_handle ->replace($this->db_table,$bind);
    }

    public function destroy($session_type,$session_id)
    {
        $bind = array(
            'session_type'=>$session_type,
            'session_id'  =>$session_id
        );
        // delete session-data
        return $this->db_handle->delete($this->db_table," `session_type` = :session_type and `session_id` = :session_id ",$bind );
    }

    public function __destruct()
    {
        // delete old sessions
        return $this->db_handle->delete($this->db_table," session_expires < ? ",time() );
    }
}
?>
