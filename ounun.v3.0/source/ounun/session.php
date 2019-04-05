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
        return true;
    }

    /**
     * @return bool
     */
    public function close()
    {
        return true;
    }

    /**
     * @param string $session_id
     * @return mixed|null
     */
    public function read($session_id)
    {
        $rs = $this->_db->table($this->_session_table)
            ->where('', '')
            ->column_one();
        if ($rs) {
            return json_decode($rs['data'], true);
        }
        return null;
    }

    /**
     * @param string $session_id
     * @param string $data
     * @return bool
     */
    public function write($session_id, $data)
    {
        $time = time() + 3600;
        $rs = $this->_db->replace('', []);
        return $rs ? true : false;
    }

    /**
     * @param string $session_id
     * @return bool|int
     */
    public function destroy($session_id)
    {
        return $this->_db->delete('');
    }

    /**
     * @param int $maxlifetime
     * @return bool
     */
    public function gc($maxlifetime)
    {
        $this->_db->delete('');
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
//            $uniqid_prefix     = '';
//            $uniqid_filename   = '/tmp/php_session_uniqid.txt';
//            if(!file_exists($uniqid_filename))
//            {
//                $uniqid_prefix = \substr(\uniqid('',false),3);
//                @file_put_contents($uniqid_filename,$uniqid_prefix);
//            }
//            if(!$uniqid_prefix)
//            {
//                if(file_exists($uniqid_filename))
//                {
//                    $uniqid_prefix = @file_get_contents($uniqid_filename);
//                }
//                if(!$uniqid_prefix)
//                {
//                    $uniqid_prefix = \substr(\uniqid('',false),3);
//                }
//            }
//            $session_id        = \uniqid($uniqid_prefix,true);
//            $this->_session_id = \substr($session_id,0,24).\substr($session_id,25);
            $this->_session_id = string\util::uniqid();
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
    }

    /**
     * @param pdo $db
     * @param string $session_table
     * @param string $session_name
     * @return session
     */
    public static function start(pdo $db, string $session_table = 'session', string $session_name = 'PHPSESSID'): session
    {
        $handler = new session($db, $session_table, $session_name);
        session_set_save_handler($handler, true);
        session_start();
        return $handler;
    }
}
