<?php

namespace ounun\mvc\controller\admin;

use c\caiji;
use extend\config_cache;
use ounun\config;
use ounun\pdo;

class data_export extends \v
{
    /** @var \ounun\mvc\model\admin\secure */
    protected $_secure = null;

    /** @var \ounun\pdo */
    public static $db_caiji;

    /**
     * data_export constructor.
     * @param $mod
     */
    public function __construct($mod)
    {
        // get key
        // print_r($_GET);
        if(empty($_GET) || empty($_GET['site_tag'])){
            $rs = error("参数有误，site_tag为空");
            out($rs);
        }
        $site_info = cc()->site_info($_GET['site_tag']);
        if(empty($site_info) || empty($site_info['api_key'])){
            $rs = error("站点标识site_tag:{$_GET['site_tag']}有误，api_key值为空");
            out($rs);
        }

        // check    -----------------
        $this->_secure = new \ounun\mvc\model\admin\secure($site_info['api_key']);
        $rs = $this->_secure->check($_GET, time());
        if(error_is($rs)){
            out($rs);
        }

        // print_r(['$site_info'=>$site_info]);
        // exit();

        // adm_purv -----------------
        $caiji_tag = $_GET['caiji_tag'];
        if ($_GET['caiji_tag']) {
            $data = config::$global['caiji'][$caiji_tag];
            if ($data && $data['db']) {
                static::$db_caiji = pdo::instance($data['db']);
            }
        }
        if (empty(static::$db_caiji)) {
            $rs = error("数据库连接失败...caiji_tag:{$_GET['caiji_tag']}");
            out($rs);
        }
        parent::__construct($mod);
    }
}
