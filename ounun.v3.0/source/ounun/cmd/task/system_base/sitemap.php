<?php

namespace ounun\cmd\task\system_base;

use ounun\cmd\task\manage;
use ounun\cmd\task\struct;

abstract class sitemap extends _system
{
    public static $name = '网站地图重新生成 [sitemap]';
    /** @var string 定时 */
    public static $crontab = '{1-59} 9 * * *';
    /** @var int 最短间隔 */
    public static $interval = 86400;
    /**
     * sitemap constructor.
     * @param struct $task_struct
     * @param string $tag
     * @param string $tag_sub
     */
    public function __construct(struct $task_struct, string $tag = '', string $tag_sub = '')
    {
        $this->_tag = 'sitemap';
        $this->_tag_sub = '';

        parent::__construct($task_struct, $tag, $tag_sub);
    }

    /**
     * 执行任务
     * @param array $input
     * @param int $mode
     * @param bool $is_pass_check
     */
    public function execute(array $input = [], int $mode = manage::Mode_Dateup, bool $is_pass_check = false)
    {
        // sleep(rand(1,10));
        try {
            $this->_ = manage::Logs_Succeed;
            $this->url_refresh();
            $this->msg("Successful sitemap");
        } catch (\Exception $e) {
            $this->_logs_state = manage::Logs_Fail;
            $this->msg($e->getMessage());
            $this->msg("Fail sitemap");
        }
    }

    /** 刷新 sitemap */
    abstract public function url_refresh();

    /**
     * @param string $url
     * @param int $xzh
     * @param string $mod
     * @param string $changefreq "always", "hourly", "daily", "weekly", "monthly", "yearly"
     * @param float $weight
     * @return array
     */
    abstract public function data(string $url, string $mod = 'page', string $changefreq = 'daily', int $xzh = 1, float $weight = 0.95);

    /**
     * @param array $bind
     * @param bool $is_update
     */
    abstract public function insert(array $bind, bool $is_update = false);

}
