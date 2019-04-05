<?php

namespace ounun\mvc\controller\admin;

use ounun\config;
use ounun\pdo;

class data_export extends \v
{
    /** @var \ounun\mvc\model\admin\secure */
    protected $_secure = null;

    /**
     * data_export constructor.
     * @param $mod
     */
    public function __construct($mod)
    {
        // check    -----------------
        $this->_secure = new \ounun\mvc\model\admin\secure(config::$app_key_communication);
        list($check, $error_msg) = $this->_secure->check($_GET, time());
        if (!$check) {
            $rs = ['ret' => $check, 'error' => $error_msg];
            $this->_secure->outs($rs);
        }

        // adm_purv -----------------
        $caiji_tag = $_GET['caiji_tag'];
        if ($_GET['caiji_tag']) {
            $data = config::$global['caiji'][$caiji_tag];
            if ($data && $data['db']) {
                static::$db_v = pdo::instance($data['db']);
            }
        }
        if (null == static::$db_v) {
            $rs = ['ret' => false, 'error' => '数据库连接失败...'];
            $this->_secure->outs($rs);
        }
        parent::__construct($mod);
    }
}
