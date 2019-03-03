<?php
namespace ounun\mvc\controller\admin;

use ounun\pdo;

class data_export extends \v
{
    /** @var \ounun\pdo */
    protected $_db_libs = null;
    /** @var \ounun\mvc\model\admin\secure */
    protected $_secure  = null;

    /**
     * data_export constructor.
     * @param $mod
     */
    public function __construct($mod)
    {
        // check    -----------------
        $this->_secure          = new \ounun\mvc\model\admin\secure(Const_Key_Conn_Private);
        list($check,$error_msg) = $this->_secure->check($_GET,time());
        if(!$check)
        {
            $rs   = ['ret'=>$check,'error'=>$error_msg];
            $this->_secure->outs($rs);
        }

        // adm_purv -----------------
        $libs_key = $_GET['libs_key'];
        if($_GET['libs_key'])
        {
            $libs = $GLOBALS['_scfg']['libs'][$libs_key];
            if($libs && $libs['db'])
            {
                $this->_db_libs   = pdo::instance($libs_key);
            }
        }
        if(null == $this->_db_libs)
        {
            $rs   = [ 'ret'  => false, 'error'=> '数据库连接失败...' ];
            $this->_secure->outs($rs);
        }
        parent::__construct($mod);
    }
}