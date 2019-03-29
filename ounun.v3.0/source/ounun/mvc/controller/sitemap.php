<?php
namespace ounun\mvc\controller;

use ounun\api_sdk\com_baidu;

class sitemap extends \v
{
    /**
     * 网站地址
     * @param $mod array
     */
    public function index($mod)
    {
        header("Content-type:text/xml");
        $this->init_page('/sitemap/index.xml',true,true,true,'',86400);

        $url_root   = "sitemap/list/";
        $com_baidu  = new com_baidu($this->_db_v,null);
        $xml        = $com_baidu->maps_index($url_root,$_SERVER['HTTP_HOST']);
        exit($xml);
    }

    /**
     * 特别URL 路由
     */
    public function list($mod)
    {
        header("Content-type:text/xml");
        $page          = (int)$mod[1];
        $this->init_page("/sitemap/list/{$page}.xml",true,true,true,'',86400);
        $com_baidu     = new com_baidu($this->_db_v,null);
        $xml           = $com_baidu->maps_page($page,$_SERVER['HTTP_HOST']);
        exit($xml);
    }
}
