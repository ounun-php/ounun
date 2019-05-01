<?php

namespace ounun\cmd\task\system_base;

use ounun\cmd\task\manage;

abstract class sitemap_make extends _system
{
    /** @var string 分类 */
    public static $tag = 'system';
    /** @var string 子分类 */
    public static $tag_sub = 'sitemap';

    /** @var string 任务名称 */
    public static $name = '网站地图重新生成 [sitemap]';
    /** @var string 定时 */
    public static $crontab = '{1-59} 9 * * *';
    /** @var int 最短间隔 */
    public static $interval = 86400;

    /**
     * 定时  数据接口提交 mip
     * @param bool $is_today
     */
    protected function _do(bool $is_today = false)
    {
        // 首页
        $bind = $this->_db_urls_data('/', 'index', 'daily', 0, 1);
        $this->_db_urls_insert($bind, true);
        // 其它 页面
        $this->url_refresh();
    }

    /**
     * @param array $urls_domain
     * @return mixed
     */
    public function _push_api(array $urls_domain)
    {
        return [0, 0];
    }

    /**
     * @param array $rs
     * @return array [$urls_path, $urls_domain]
     */
    protected function _urls(array $rs)
    {
        return [0, 0];
    }

    /** 刷新 sitemap */
    abstract public function url_refresh();
}
