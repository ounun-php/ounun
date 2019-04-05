<?php

namespace ounun\mvc\controller\api;

use ounun\config;

class api extends \v
{
    /**
     * 后台数据接口
     * @param $mod
     */
    public function interface_mysql($mod)
    {
        $this->init_page("/api/interface_mysql.html", false, false);

        $secure = new \ounun\mvc\model\admin\secure(config::$app_key_communication);
        list($check, $error_msg) = $secure->check($_GET, time());
        if ($check) {
            $db = \ounun\config::$database[\ounun\config::$app_name];
            $data = $secure->encode($db);
            $rs = ['ret' => $check, 'data' => $data];
        } else {
            $rs = ['ret' => $check, 'error' => $error_msg];
        }
        exit(json_encode($rs, JSON_UNESCAPED_UNICODE));
    }
}
