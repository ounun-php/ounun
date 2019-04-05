<?php

namespace extend;

use ounun\pdo;

class config_cache extends \ounun\cache\config
{
    /** zqun */
    public function zqun()
    {
        return $this->_data('zqun', '_zqun_mysql');
    }

    public function zqun_clean()
    {
        $this->_clean('zqun');
    }

    protected function _zqun_mysql($args)
    {
        return $this->_db->table('`sys_site_zqun`')
            ->field('*')
            ->assoc('zqun_tag')
            ->order('`type`', pdo::Order_Desc)
            ->column_all();
    }

    /** host */
    public function host()
    {
        return $this->_data('host', '_host_mysql');
    }

    public function host_clean()
    {
        $this->_clean("host");
    }

    protected function _host_mysql($args)
    {
        return $this->_db->table('`sys_host`')
            ->field('*')
            ->assoc('host_tag')
            ->order('`host_type`', pdo::Order_Desc)
            ->order('`host_tag`', pdo::Order_Asc)
            ->column_all();
    }

    /** site */
    public function site()
    {
        return $this->_data('site', '_site_mysql');
    }

    public function site_clean()
    {
        $this->_clean("site");
    }

    protected function _site_mysql($args)
    {
        $rs = $this->_db->table('`sys_site_info`')
            ->field('*')
            ->order('`type`', pdo::Order_Desc)
            ->order('`zqun_tag`', pdo::Order_Asc)
            ->order('`site_tag`', pdo::Order_Asc)
            ->column_all();
        $data = [];
        foreach ($rs as $v) {
            $data[$v['zqun_tag']][$v['site_tag']] = $v;
        }
        return $data;
    }
}