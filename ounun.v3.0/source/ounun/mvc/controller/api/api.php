<?php

namespace ounun\mvc\controller\api;

use ounun\config;
use ounun\mvc\c;

class api extends \v
{
    /**
     * 同步数据库mysql配制
     * @param $mod
     */
    public function interface_mysql($mod)
    {
        $this->init_page("/api/interface_mysql.html", false, true);

        $secure = $this->_secure_check();
        $db_config = config::$database[config::database_default_get()];
        $data = $secure->encode($db_config);
        out(succeed($data), c::Format_Json);
    }

    /**
     * 同步host配制
     * @param $mod
     */
    public function interface_hosts($mod)
    {
        $this->init_page("/api/interface_hosts.html", false, true);

        $secure = $this->_secure_check();
        $url_www = substr(config::$url_www, 0, -1);
        $url_wap = substr(config::$url_wap, 0, -1);
        $url_mip = substr(config::$url_mip, 0, -1);
        $url_api = substr(config::$url_api, 0, -1);
        $url_static = substr(config::$url_static, 0, -1);

        $data = [
            'url_www' => $url_www,
            'url_wap' => $url_wap,
            'url_mip' => $url_mip,
            'url_api' => $url_api,
            'url_static' => $url_static,

            'domain_www' => explode('/',$url_www,3)[2],
            'domain_wap' =>  explode('/',$url_wap,3)[2],
            'domain_mip' =>  explode('/',$url_mip,3)[2],
            'domain_api' =>  explode('/',$url_api,3)[2],
            'domain_static' =>  explode('/',$url_static,3)[2],
        ];
        $data = $secure->encode($data);
        out(succeed($data), c::Format_Json);
    }

    /**
     * 同步stat配制
     * @param $mod
     */
    public function interface_stat($mod)
    {
        $this->init_page("/api/interface_stat.html", false, true);

        $secure = $this->_secure_check();
        $data = config::$global['stat'];
        $data = $secure->encode($data);
        out(succeed($data), c::Format_Json);
    }

    /**
     * 同步seo配制
     * @param $mod
     */
    public function interface_seo($mod)
    {
        $this->init_page("/api/interface_seo.html", false, true);

        $secure = $this->_secure_check();
        $data = config::$global['seo'];
        $data = $secure->encode($data);
        out(succeed($data), c::Format_Json);
    }

    /**
     * @return \ounun\mvc\model\admin\secure
     */
    protected function _secure_check()
    {
        $secure = new \ounun\mvc\model\admin\secure(config::$app_key_communication);
        $rs = $secure->check($_GET, time());
        if (error_is($rs)) {
            exit(json_encode_unescaped($rs));
        }
        return $secure;
    }
}
