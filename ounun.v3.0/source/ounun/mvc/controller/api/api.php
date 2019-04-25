<?php

namespace ounun\mvc\controller\api;

use ounun\config;
use ounun\mvc\c;

class api extends \v
{
    /**
     * 后台数据接口
     * @param $mod
     */
    public function interface_mysql($mod)
    {
        $this->init_page("/api/interface_mysql.html", false, true);

        $secure = new \ounun\mvc\model\admin\secure(config::$app_key_communication);
        $rs = $secure->check($_GET, time());
        if(error_is($rs)){
            exit(json_encode_unescaped($rs));
        }
        $db_config = \ounun\config::$database[\ounun\config::database_default_get()];
        $data = $secure->encode($db_config);
        out(succeed($data),c::Format_Json);
    }
}
