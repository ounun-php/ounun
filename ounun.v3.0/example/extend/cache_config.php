<?php
/**
 * Created by PhpStorm.
 * User: dreamxyp
 * Date: 2018/12/23
 * Time: 21:39
 */

namespace extend;


use ounun\config;

class cache_config extends \ounun\cache\config
{
    /**
     * @param \ounun\pdo $db
     * @param string $tag
     * @return cache_config
     */
    static public function instance(\ounun\pdo $db,string $tag='tag'):cache_config
    {
        if(!self::$_inst[$tag])
        {
            self::$_inst[$tag] = new cache_config($db,config::$global['cache_file'],$tag);
        }
        return self::$_inst[$tag];
    }

    /** zqun */
    public function zqun()
    {
        return $this->_data("zqun","_zqun_mysql");
    }
    public function zqun_clean()
    {
        $this->_clean("zqun");
    }
    protected function _zqun_mysql($args)
    {
        return $this->_db->fetch_assoc('SELECT * FROM  `adm_zqun` ORDER BY `type` DESC, `zqun_tag` ;',null,'zqun_tag');
    }

    /** host */
    public function host()
    {
        return $this->_data("host","_host_mysql");
    }
    public function host_clean()
    {
        $this->_clean("host");
    }
    protected function _host_mysql($args)
    {
        return $this->_db->fetch_assoc('SELECT * FROM  `adm_host` ORDER BY `host_type` DESC, `host_tag` ;',null,'host_tag');
    }

    /** site */
    public function site()
    {
        return $this->_data("site","_site_mysql");
    }
    public function site_clean()
    {
        $this->_clean("site");
    }
    protected function _site_mysql($args)
    {
        $rs   = $this->_db->data_array('SELECT * FROM  `adm_site_info` ORDER BY `type` DESC, `zqun_tag` ASC , `site_tag` ASC ;');
        $data = [];
        foreach ($rs as $v)
        {
            $data[$v['zqun_tag']][$v['site_tag']] = $v;
        }
        return $data;
    }
}