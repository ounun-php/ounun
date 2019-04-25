<?php

namespace ounun\cmd\task\system_base;

use ounun\cmd\console;
use ounun\cmd\task\libs\com_baidu;
use ounun\cmd\task\manage;
use ounun\cmd\task\task_base;
use ounun\config;
use ounun\mvc\model\admin\purview;
use ounun\pdo;

abstract class _system extends task_base
{
    /** @var string 分类 */
    public static $tag = 'system';
    /** @var string 子分类 */
    public static $tag_sub = 'sitemap';

    /** @var string 任务名称 */
    public static $name = '网站地图/提交';
    /** @var string 定时 */
    public static $crontab = '{1-59} 11 * * *';
    /** @var int 最短间隔 */
    public static $interval = 86400;
    /** @var string 类型 */
    public static $site_type = purview::app_type_site;

    /**
     * @return array
     */
    public function status()
    {
        $this->_logs_status = manage::Logs_Fail;
        manage::logs_msg("error:" . __METHOD__, $this->_logs_status);
        return [];
    }

    /**
     * @param array $input
     * @param int $mode
     * @param bool $is_pass_check
     */
    public function execute(array $input = [], int $mode = manage::Mode_Dateup, bool $is_pass_check = false)
    {
        console::echo(__METHOD__, console::Color_Red);
        try {
            $this->_logs_status = manage::Logs_Succeed;

            manage::logs_msg("Successful update:{$this->_task_struct->task_id}/{$this->_task_struct->task_name}", $this->_logs_status);
        } catch (\Exception $e) {
            $this->_logs_status = manage::Logs_Fail;
            manage::logs_msg($e->getMessage(),$this->_logs_status);
            manage::logs_msg('Fail Coll tag:'.static::$tag.' tag_sub:'.static::$tag_sub, manage::Logs_Fail);
        }
    }


    /** @var int 单次提交数据 */
    protected $_push_step = 1000;

    protected $_url_root_pc = '';
    protected $_url_root_mip = '';
    protected $_url_root_wap = '';

    protected $_domain_pc = '';
    protected $_domain_mip = '';
    protected $_domain_wap = '';

    protected $_token_site = '';
    protected $_token_xzh = '';
    protected $_appid_xzh = '';

    public function config_set(string $db_tag = '', array $db_config = [])
    {
        $config = $this->config();
        if ($config['domain_pc']) {
            $this->_domain_pc = $config['domain_pc'];
            $this->_url_root_pc = "https://{$this->_domain_pc}";
        }
        if ($config['domain_mip']) {
            $this->_domain_mip = $config['domain_mip'];
            $this->_url_root_mip = "https://{$this->_domain_mip}";
        }
        if ($config['domain_mip']) {
            $this->_domain_wap = $config['domain_wap'];
            $this->_url_root_wap = "https://{$this->_domain_wap}";
        }
        if ($config['token_site']) {
            $this->_token_site = $config['token_site'];
        }
        if ($config['token_xzh']) {
            $this->_token_xzh = $config['token_xzh'];
        }
        if ($config['appid_xzh']) {
            $this->_appid_xzh = $config['appid_xzh'];
        }
    }

    /**
     * @param $type
     * @param $is_today
     * @return bool
     */
    protected function _do_push($type, $is_today)
    {
        $max = com_baidu::push_max[$type];
        $Ymd = date('Ymd');
        $time = time();
        if ($max) {
            $cc = $this->_db_site->query("SELECT COUNT(`url_id`) as `cc` FROM {$this->_table_sitemap_push} WHERE `Ymd` = :Ymd and `target_id` = :target_id ;", ['Ymd' => $Ymd, 'target_id' => $type])->column_one();
            $cc = (int)$cc['cc'];
            // echo $db->sql()."\n";
            manage::logs_msg("COUNT(`url_id`) as cc:{$cc} " . com_baidu::type[$type]);
            if ($cc < $max) {
                $step_cc0 = $max - $cc;
                $step_cc = $step_cc0 > $this->_push_step ? $this->_push_step : $step_cc0;
                $Ymd_start = date('Ymd', $time - com_baidu::push_rate);
                $where_xzh = '';
                if ($type == com_baidu::type_baidu_xzh_realtime) {
                    $where_xzh = " `xzh` = 1 and ";
                }
                if ($is_today) {
                    $today_time = $time - 3600 * 24;
                    $bind = ['Ymd' => $Ymd_start, 'target_id' => $type, 'lastmod' => $today_time];
                    $rs = $this->_db_site->query("SELECT * FROM {$this->_table_sitemap} WHERE {$where_xzh} `lastmod` > :lastmod and `url_id` NOT in ( SELECT `url_id` FROM {$this->_table_sitemap_push} WHERE `Ymd` >= :Ymd and `target_id` = :target_id ) ORDER BY `lastmod` DESC LIMIT 0," . $step_cc . ";", $bind)->column_all();
                } else {
                    $bind = ['Ymd' => $Ymd_start, 'target_id' => $type];
                    $rs = $this->_db_site->query("SELECT * FROM {$this->_table_sitemap} WHERE {$where_xzh} `url_id` NOT in ( SELECT `url_id` FROM {$this->_table_sitemap_push} WHERE `Ymd` >= :Ymd and `target_id` = :target_id ) ORDER BY `lastmod` DESC LIMIT 0," . $step_cc . ";", $bind)->column_all();
                }
                if ($rs) {
                    $url_root = $this->_url_root_pc;
                    if ($type == com_baidu::type_baidu_pc) {
                        $url_root = $this->_url_root_pc;
                    } elseif ($type == com_baidu::type_baidu_mip) {
                        $url_root = $this->_url_root_mip;
                    } elseif ($type == com_baidu::type_baidu_wap) {
                        $url_root = $this->_url_root_wap;
                    } elseif ($type == com_baidu::type_baidu_xzh_realtime) {
                        $url_root = $this->_url_root_mip;
                    } elseif ($type == com_baidu::type_baidu_xzh_batch) {
                        $url_root = $this->_url_root_mip;
                    }
                    // -------------------------------------
                    $urls = [];
                    $urls_domain = [];
                    foreach ($rs as $v) {
                        $urls[] = $v['loc'];
                        $urls_domain[] = $url_root . $v['loc'];
                    }
                    if ($urls_domain) {
                        if ($type == com_baidu::type_baidu_pc) {
                            $rs2 = $this->push_pc($urls_domain);
                            $success = (int)$rs2['success'];
                            $remain = (int)$rs2['remain'];
                            $max_push_step = com_baidu::max_push_step;
                        } elseif ($type == com_baidu::type_baidu_wap) {
                            $rs2 = $this->push_wap($urls_domain);
                            $success = (int)$rs2['success'];
                            $remain = (int)$rs2['remain'];
                            $max_push_step = com_baidu::max_push_step;
                        } elseif ($type == com_baidu::type_baidu_mip) {
                            $rs2 = $this->push_mip($urls_domain);
                            $success = (int)$rs2['success_mip'];
                            $remain = (int)$rs2['remain_mip'];
                            $max_push_step = com_baidu::max_push_step;
                        } elseif ($type == com_baidu::type_baidu_xzh_realtime) {
                            $rs2 = $this->push_xzh_realtime($urls_domain);
                            $success = (int)$rs2['success_realtime'];
                            $remain = (int)$rs2['remain_realtime'];
                            $max_push_step = com_baidu::max_push_xzh_doday;
                        } elseif ($type == com_baidu::type_baidu_xzh_batch) {
                            $rs2 = $this->push_xzh_batch($urls_domain);
                            $success = (int)$rs2['success_batch'];
                            $remain = (int)$rs2['remain_batch'];
                            $max_push_step = com_baidu::max_push_step;
                        }

                        manage::logs_msg("cc:" . count($urls_domain) . " type:{$type} success:{$success} push_step:{$this->_push_step}");
                        $this->_push_step = $remain > $max_push_step ? $max_push_step : $remain;
                        if ($success == count($urls_domain)) {
                            $this->db_sitemap_push($urls, $type);
                        }
                    }
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @param string $url
     * @param int $xzh
     * @param string $mod
     * @param string $changefreq "always", "hourly", "daily", "weekly", "monthly", "yearly"
     * @param float $weight
     * @return array
     */
    public function sitemap_data(string $url, string $mod = 'page', string $changefreq = com_baidu::changefreq_daily, int $xzh = 1, float $weight = 0.95)
    {
        $time = time();
        return [
            'loc' => $url,
            'xzh' => $xzh,
            'mod' => $mod,
            'time_add' => $time,
            'changefreq' => $changefreq,
            'lastmod' => $time,
            'weight' => $weight,
        ];
    }

    /**
     * @param array $bind
     * @param bool $is_update
     */
    public function db_sitemap(array $bind, bool $is_update = false)
    {
        if ($is_update && $bind['lastmod']) {
            $this->_db_site->table($this->_table_sitemap)->duplicate(['lastmod' => pdo::Update_Update])->insert($bind);
        } else {
            $this->_db_site->table($this->_table_sitemap)->option('IGNORE')->insert($bind);
        }
    }

    /**
     * @param array $urls
     * @param int $type
     */
    public function db_sitemap_push(array $urls, int $type, int $Ymd = 0)
    {
        $Ymd = $Ymd ? $Ymd : date('Ymd');
        $this->_db_site->query("INSERT INTO {$this->_table_sitemap_push} (`url_id`, `Ymd`, `target_id`) SELECT `url_id`,{$Ymd},{$type} FROM {$this->_table_sitemap} WHERE `loc` in ( ? );", $urls)->affected();
    }

    /**
     * @param $api
     * @param $urls
     * @return mixed
     */
    protected function _push($api, $urls)
    {
        // $this->msg("urls:".count($urls)." api:{$api}");
        $ch = curl_init();
        $options = [
            CURLOPT_URL => $api,
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS => implode("\n", $urls),
            CURLOPT_HTTPHEADER => ['Content-Type: text/plain'],
        ];
        curl_setopt_array($ch, $options);
        $result = curl_exec($ch);
        // $this->msg($result .$urls[0]);
        // $this->msg($result );
        return json_decode($result, true);
    }


    /**
     * @param string $host
     * @return string
     */
    protected function _maps_url(string $host = '')
    {
        switch ($host) {
            case $this->_domain_pc:
                return $this->_url_root_pc;
                break;
            case $this->_domain_wap:
                return $this->_url_root_wap;
                break;
            case $this->_domain_mip:
                return $this->_url_root_mip;
                break;
            default:
                return $this->_url_root_pc;
                break;
        }
    }

    /**
     * @return array
     */
    protected function config()
    {
        return [
            'domain_pc' => explode('/', config::$url_www)[2],
            'domain_mip' => explode('/', config::$url_mip)[2],
            'domain_wap' => explode('/', config::$url_wap)[2],
            'token_xzh' => config::$global['baidu']['xzh_token'],// Const_Baidu_Xzh_Token ,
            'token_site' => config::$global['baidu']['token'],// Const_Baidu_Token ,
            'appid_xzh' => config::$global['baidu']['xzh_site_id'],//  Const_Baidu_Xzh_SiteId ,
        ];
    }
}