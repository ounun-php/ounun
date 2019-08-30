<?php

namespace ounun;


/**
 * Class session
 *
 *  CREATE TABLE `session` ( `id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,`expires` datetime NOT NULL,`data` text COLLATE utf8_unicode_ci, PRIMARY KEY (`id`) ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
 *
 * @package ounun
 */
class session implements \SessionHandlerInterface, \SessionUpdateTimestampHandlerInterface
{
    /** @var pdo */
    private $_db;

    /** @var string */
    private $_session_table = 'session';

    /** @var string 唯一ID uniqid */
    private $_session_id = '';

    /** @var string 用在 cookie 或者 URL 中的会话名称 */
    private $_session_name = 'PHPSESSID';

    /**
     * session constructor.
     * @param pdo $db
     * @param string $session_table
     * @param string $session_name
     */
    public function __construct(pdo $db, string $session_table, string $session_name)
    {
        $this->_db = $db;
        $this->_session_table = $session_table;
        $this->_session_name = $session_name;
    }

    /**
     * @param string $save_path
     * @param string $session_name
     * @return bool
     */
    public function open($save_path, $session_name)
    {
        // print_r([$save_path, $session_name]);
        return true;
    }

    /**
     * @return bool
     */
    public function close()
    {
        // session_write_close();
        return true;
    }

    /**
     * @param string $session_id
     * @return mixed|null
     */
    public function read($session_id)
    {
        $rs = $this->_db->table($this->_session_table)
            ->where(' `session_id` =:session_id ', ['session_id'=>$session_id])
            ->column_one();
        if ($rs && $rs['data']) {
            return json_decode_array($rs['data']);
        }
        return '';

//        if (!isset($session_id)) {
//            $session_id='';
//        }
//
//        try {
//            $sql="
//                SELECT
//                    sess_data
//                FROM
//                    ue_user_session
//                WHERE
//                    sess_id = :sess_id
//            ";
//            $stmt = $this->db->prepare($sql);
//            $stmt->bindParam(':sess_id', $session_id);
//            $stmt->execute();
//            $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
//            if (count($res) <> 1 ) {
//                return false;
//            } else {
//                return $res[0]['sess_data'];
//            }
//        }
//        catch (PDOException $e) {
//            error_log('Error reading the session data table in the session reading method.',3,"C:\wamp\www\universal_empires\test_log.txt");
//            error_log(" Query with error: $sql",3,"C:\wamp\www\universal_empires\test_log.txt");
//            error_log(" Reason given: $e->getMessage()",3,"C:\wamp\www\universal_empires\test_log.txt");
//            return false;
//        }
    }

    /**
     * @param string $session_id
     * @param string $data
     * @return bool
     */
    public function write($session_id, $data)
    {
        // print_r(['$data'=>$data]);
        $time = time() + 3600;
        $uid  = $data?(int)$data['uid']:0;
        $data = $data?json_encode_unescaped($data):'';
        $bind = [
            'session_id'=>$session_id,
            'uid'=> $uid,
            'expires'=>$time,
            'data'=>$data
        ];
        $rs = $this->_db->table($this->_session_table)->replace(true)->insert($bind);
        return $rs ? true : false;

//        if (isset($_SESSION['user_id'])) {
//            $user_id = (int) $_SESSION['user_id'];
//        } else {
//            $user_id= (int) 0;
//        }
//
//        try {
//            $sql="
//                SELECT
//                    sess_data
//                FROM
//                    ue_user_session
//                WHERE
//                    sess_id = :sess_id
//            ";
//            $stmt = $this->db->prepare($sql);
//            $stmt->bindParam(':sess_id', $session_id);
//            $stmt->execute();
//            $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
//
//        }
//        catch (PDOException $e) {
//            error_log('Error reading the session data table in the session reading method.',3,"C:\wamp\www\universal_empires\test_log.txt");
//            error_log(" Query with error: $sql",3,"C:\wamp\www\universal_empires\test_log.txt");
//            error_log(" Reason given: $e->getMessage()",3,"C:\wamp\www\universal_empires\test_log.txt");
//            return false;
//        }
//
//        if (empty($res)) {
//            try {
//                if (count($res) === 0) {
//
//                    $sql="
//                        INSERT INTO
//                            ue_user_session
//                        (
//                              sess_id
//                            , user
//                            , start
//                            , last_activity
//                            , expires
//                            , sess_data
//                        )
//                        VALUES
//                            (
//                                  :sess_id
//                                , 0
//                                , NOW()
//                                , NOW()
//                                , DATE_ADD(NOW(), INTERVAL 30 MINUTE)
//                                , :sess_data
//                            )
//                    ";
//
//                    $stmt = $this->db->prepare($sql);
//                    $stmt->bindParam(':sess_id', $session_id);
//                    $stmt->bindParam(':sess_data', $session_data);
//                    $stmt->execute();
//                }
//            }
//            catch (PDOException $e) {
//                error_log('Error reading the session data table in the session reading method.',3,"C:\wamp\www\universal_empires\test_log.txt");
//                error_log(" Query with error: $sql",3,"C:\wamp\www\universal_empires\test_log.txt");
//                error_log(" Reason given: $e->getMessage()",3,"C:\wamp\www\universal_empires\test_log.txt");
//                return false;
//            }
//        } else {
//
//            try {
//                $sql="
//                    UPDATE
//                        ue_user
//                    SET
//                        last_activity = NOW()
//                    WHERE
//                        id =  :user_id
//                ";
//                $stmt = $this->db->prepare($sql);
//                $stmt->bindParam(':user_id', $user_id);
//                $stmt->execute();
//            }
//            catch (PDOException $e) {
//                error_log('Error reading the session data table in the session reading method.',3,"C:\wamp\www\universal_empires\test_log.txt");
//                error_log(" Query with error: $sql",3,"C:\wamp\www\universal_empires\test_log.txt");
//                error_log(" Reason given: $e->getMessage()",3,"C:\wamp\www\universal_empires\test_log.txt");
//                return false;
//            }
//            try {
//                $sql="
//                    UPDATE
//                        ue_user_session
//                    SET
//                          last_activity = NOW()
//                        , expires = DATE_ADD(NOW(), INTERVAL 30 MINUTE)
//                        , sess_data = :sess_data
//                        , user = :user_id
//                    WHERE
//                        sess_id = :sess_id
//                ";
//
//                $stmt = $this->db->prepare($sql);
//                $stmt->bindParam(':sess_data', $session_data);
//                $stmt->bindParam(':user_id', $user_id);
//                $stmt->bindParam(':sess_id', $session_id);
//                $stmt->execute();
//                return true;
//            }
//            catch (PDOException $e) {
//                error_log('Error reading the session data table in the session reading method.',3,"C:\wamp\www\universal_empires\test_log.txt");
//                error_log(" Query with error: $sql",3,"C:\wamp\www\universal_empires\test_log.txt");
//                error_log(" Reason given: $e->getMessage()",3,"C:\wamp\www\universal_empires\test_log.txt");
//                return false;
//            }
//        }
    }

    /**
     * @param string $session_id
     * @return bool|int
     */
    public function destroy($session_id)
    {
        try {
//            $sql="
//                DELETE FROM
//                    ue_user_session
//                WHERE
//                    sess_id = :sess_id
//            ";
//            $stmt = $this->db->prepare($sql);
//            $stmt->bindParam(':sess_id', $session_id);
//            $stmt->execute();
            return true;
        }
        catch (PDOException $e) {
//            error_log('Error reading the session data table in the session reading method.',3,"C:\wamp\www\universal_empires\test_log.txt");
//            error_log(" Query with error: $sql",3,"C:\wamp\www\universal_empires\test_log.txt");
//            error_log(" Reason given: $e->getMessage()",3,"C:\wamp\www\universal_empires\test_log.txt");
            return false;
        }
    }

    /**
     * @param int $maxlifetime
     * @return bool
     */
    public function gc($maxlifetime)
    {
//        // $this->_db->table($this->_session_table);
//        try {
//            $sql="
//                DELETE FROM
//                    ue_user_session
//                WHERE
//                    last_activity < expires
//            ";
//            $stmt = $this->db->prepare($sql);
//            $stmt->execute();
//        }
//        catch (PDOException $e) {
//            error_log('Error reading the session data table in the session reading method.',3,"C:\wamp\www\universal_empires\test_log.txt");
//            error_log(" Query with error: $sql",3,"C:\wamp\www\universal_empires\test_log.txt");
//            error_log(" Reason given: $e->getMessage()",3,"C:\wamp\www\universal_empires\test_log.txt");
//            return false;
//        }

        return true;
    }

    /**
     * @return string
     */
    public function create_sid()
    {
        if ($this->_session_id) {
            return $this->_session_id;
        } elseif ($_REQUEST[$this->_session_name] && 32 == strlen($_REQUEST[$this->_session_name])) {
            $this->_session_id = $_REQUEST[$this->_session_name];
        } elseif ($_COOKIE[$this->_session_name] && 32 == strlen($_COOKIE[$this->_session_name])) {
            $this->_session_id = $_COOKIE[$this->_session_name];
        } else {
            $this->_session_id = tool\str::uniqid();
        }
        return $this->_session_id;
    }

    public function validateId($session_id)
    {
        if (32 == strlen($session_id)) {
            return $session_id;
        }
        return $this->_session_id;
    }

    /**
     * @param string $session_id
     * @param string $session_data
     * @return bool|void
     */
    public function updateTimestamp($session_id, $session_data)
    {
        // implements SessionUpdateTimestampHandlerInterface::validateId()
        // available since PHP 7.0
        // return value should be true for success or false for failure
        // ...
        return true;
    }

    public function __destruct()
    {
        session_write_close();
    }

    /**
     * @param pdo $db
     * @param string $session_table
     * @param string $session_name
     * @return session
     */
    public static function start(?pdo $db = null, string $session_table = 'v1_system_session', string $session_name = 'PHPSESSID'): session
    {
        if(empty($db)){
            $db  = \v::db_v_get();
        }
        session_write_close();
        $handler = new session($db, $session_table, $session_name);
        session_set_save_handler($handler,true);
        //
        // Warning: session_write_close(): Failed to write session data using user defined save handler. (session.save_path: ) in Unknown on line
        // register_shutdown_function( [$handler, 'close']);

        session_start();
        return $handler;
    }

    /**
     * @return string
     */
    public static function start_simple()
    {
        session_start();
        $session_id = $_REQUEST['session_id'];
        if($session_id){
            session_id($session_id);
        }else{
            $session_id = session_id();
        }
        return $session_id;
    }
}
