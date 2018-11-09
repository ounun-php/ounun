<?php
namespace task\_75;

use ounun\logs;

class sitemap extends \task\base_system_sitemap
{
    /**
     * 刷新 sitemap
     * @param $mod
     */
    public function url_refresh()
    {
        // 列表
        $bind = $this->_data('/','index','daily',0,1);
        $this->_insert($bind,true);
        //
        $this->_url_refresh2();
    }


    /**
     *
     * 数据接口  输出接口
     *
     * @param $mod
     */
    protected function _url_refresh2()
    {

    }
}
