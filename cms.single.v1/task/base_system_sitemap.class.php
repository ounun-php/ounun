<?php
namespace task;


use ounun\logs;

class base_system_sitemap extends base
{

    /** @var \api_sdk\com_baidu  */
    protected $_baidu_sdk = null;
    /**
     * 执行任务
     * @param array $paras
     * @param bool  $is_check
     */
    public function run(array $paras=[],bool $is_check = false)
    {
        if( !$this->check($is_check) ) { return ; }
  
        $this->_tag       = 'sitemap';
        $this->_tag_sub   = '';
        $this->logs_init($this->_tag,$this->_tag_sub);
        $this->_baidu_sdk = new \api_sdk\com_baidu($this->_db,$this->_logs);
        // sleep(rand(1,10));
        try {
            $this->_logs_state = \ounun\logs::state_ok;
            $this->url_refresh();
            $this->msg("Successful sitemap");
        } catch (\Exception $e) {
            $this->_logs_state = \ounun\logs::state_fail;
            $this->msg($e->getMessage());
            $this->msg("Fail sitemap");
        }
    }

    /** 刷新 sitemap */
    protected function url_refresh()
    {
        $this->_logs_state = logs::state_fail;
        $this->msg("Fail url_refresh没定义");
    }

    /**
     * @param string $url
     * @param int    $xzh
     * @param string $mod
     * @param string $changefreq  "always", "hourly", "daily", "weekly", "monthly", "yearly"
     * @param float  $weight
     * @return array
     */
    protected function _data(string $url,string $mod = 'page',string $changefreq = 'daily',int $xzh = 1,float $weight = 0.95)
    {
        return $this->_baidu_sdk->sitemap_data($url,$mod,$changefreq,$xzh,$weight);
    }

    /**
     * @param array $bind
     * @param bool $is_update
     */
    protected function _insert(array $bind, bool $is_update = false)
    {
        $this->_baidu_sdk->db_sitemap($bind,$is_update);
    }
}