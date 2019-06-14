<?php
namespace ounun\cmd\task;

use ounun\api_sdk\com_baidu;
use ounun\mvc\model\admin\purview;

abstract class task_base_system extends task_base
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
     * @param array $argc_input
     * @param int $argc_mode
     * @param bool $is_pass_check
     */
    public function execute(array $argc_input = [], int $argc_mode = manage::Mode_Dateup, bool $is_pass_check = false)
    {
        $is_today = false;
        try {
            $this->_config_set();
            $this->_do($is_today);
            $this->_logs_status = manage::Logs_Succeed;
            manage::logs_msg("Successful system:{$this->_task_struct->task_id}/{$this->_task_struct->task_name}", $this->_logs_status, __FILE__, __LINE__, time());
        } catch (\Exception $e) {
            $this->_logs_status = manage::Logs_Fail;
            manage::logs_msg($e->getMessage(), $this->_logs_status, __FILE__, __LINE__, time());
            manage::logs_msg('Fail Coll tag:' . static::$tag . ' tag_sub:' . static::$tag_sub, manage::Logs_Fail, __FILE__, __LINE__, time());
        }
    }

    /** @var string Www URL */
    protected $_url_root_www = '';
    /** @var string Mip URL */
    protected $_url_root_mip = '';
    /** @var string Mobile URL */
    protected $_url_root_wap = '';

    /**  @var string  表格 所有的URL  网站地图 */
    protected $_table_sitemap_urls = ' `v1_core_sitemap` ';
    /**  @var string  表格 URL提交 */
    protected $_table_sitemap_push = ' `v1_core_sitemap_push` ';

    /** @var int 单次提交数据 */
    protected $_push_step = 1000;
    /** @var int 单次提交数据(最大) */
    protected $_push_step_max = 0;
    /** @var array SEO */
    protected $_seo = [
        'baidu_token' => '', // Baidu API
        'baidu_xzh_site_id' => '', // SiteId_Xzh API
        'baidu_xzh_token' => '', // Const_Baidu_Xzh_Token API
    ];

    protected function _config_set()
    {
        $site_info = $this->_site_info_get();
        if ($site_info['dns']) {
            $dns = json_decode($site_info['dns'], true);
            if ($dns && is_array($dns)) {
                foreach ($dns as $v) {
                    if ('www' == $v['tag']) {
                        $this->_url_root_www = "https://{$v['sub_domain']}";
                    } elseif ('mip' == $v['tag']) {
                        $this->_url_root_mip = "https://{$v['sub_domain']}";
                    } elseif ('wap' == $v['tag']) {
                        $this->_url_root_wap = "https://{$v['sub_domain']}";
                    }
                }
            }
        }
        if ($site_info['config_seo']) {
            $seo = json_decode($site_info['config_seo'], true);
            if ($seo && is_array($seo)) {
                $this->_seo = $seo;
            }
        }
//        print_r([
//            'www' => $this->_url_root_www,
//            'mip' => $this->_url_root_mip,
//            'wap' => $this->_url_root_wap,
//            'seo' => $this->_seo,
//        ]);
    }

    /** */
    protected function _do(bool $is_today = false)
    {
        manage::logs_msg(__METHOD__ . "->没定义 \$is_today:{$is_today}", manage::Logs_Fail, __FILE__, __LINE__, time());
    }

    /**
     * @param int $type
     * @param int $is_today
     * @return bool
     */
    protected function _do_push(int $type, int $is_today)
    {
        $max = com_baidu::push_max[$type];
        $Ymd = date('Ymd');
        $time = time();
        if ($max) {
            /** @var int $cc 当天已提交数据 */
            $cc = manage::db_site()->table($this->_table_sitemap_push)
                ->where(' `Ymd` = :Ymd and `target_id` = :target_id ', ['Ymd' => $Ymd, 'target_id' => $type])
                ->count_value(' `url_id` ');

            // echo $db->sql()."\n";
            manage::logs_msg("COUNT(`url_id`) as 当天已提交数据-cc:{$cc} -type:" . com_baidu::type[$type], $this->_logs_status, __FILE__, __LINE__, time());
            if ($cc < $max) {
                $step_cc0 = $max - $cc;
                $step_cc1 = $step_cc0 > $this->_push_step ? $this->_push_step : $step_cc0;
                $Ymd_start = date('Ymd', $time - com_baidu::push_rate);
                $where_xzh = '';
                if ($type == com_baidu::type_baidu_xzh_realtime) {
                    $where_xzh = " `xzh` = 1 and ";
                }
                if ($is_today) {
                    $today_time = $time - 3600 * 24;
                    $bind = ['Ymd' => $Ymd_start, 'target_id' => $type, 'lastmod' => $today_time];
                    $rs = manage::db_site()->query("SELECT * FROM {$this->_table_sitemap_urls} WHERE {$where_xzh} `lastmod` > :lastmod and `url_id` NOT in ( SELECT `url_id` FROM {$this->_table_sitemap_push} WHERE `Ymd` >= :Ymd and `target_id` = :target_id ) ORDER BY `lastmod` DESC LIMIT 0," . $step_cc1 . ";", $bind)->column_all();
                } else {
                    $bind = ['Ymd' => $Ymd_start, 'target_id' => $type];
                    $rs = manage::db_site()->query("SELECT * FROM {$this->_table_sitemap_urls} WHERE {$where_xzh} `url_id` NOT in ( SELECT `url_id` FROM {$this->_table_sitemap_push} WHERE `Ymd` >= :Ymd and `target_id` = :target_id ) ORDER BY `lastmod` DESC LIMIT 0," . $step_cc1 . ";", $bind)->column_all();
                }
                if ($rs) {
                    /**
                     * @var array $urls_path    相对URL
                     * @var array $urls_domain  全部带Http
                     */
                    list($urls_path,$urls_domain) = $this->_urls($rs);
                    if ($urls_domain) {
                        /**
                         * @var int $success 提交成功数量
                         * @var int $remain  剩余数量
                         */
                        list($success,$remain) = $this->_push_api($urls_domain);

                        // $urls_domain
                        manage::logs_msg("cc:" . count($urls_domain) . " type:{$type} success:{$success} push_step:{$this->_push_step}", manage::Logs_Normal, __FILE__, __LINE__, time());

                        if ($success == count($urls_domain)) {
                            $this->_db_push_insert($urls_path,$type,$Ymd);
                        }
                        // 设定下次 提交数据
                        $this->_push_step = $remain > $this->_push_step_max ? $this->_push_step_max : $remain;
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
    protected function _db_urls_data(string $url, string $mod = 'page', string $changefreq = 'daily', int $xzh = 1, float $weight = 0.95, int $time = 0)
    {
        if (empty($time)) {
            $time = time();
        }
        return ['loc' => $url, 'xzh' => $xzh, 'mod' => $mod, 'time_add' => $time, 'changefreq' => $changefreq, 'lastmod' => $time, 'weight' => $weight,];
    }

    /**
     * @param array $bind
     * @param bool $is_update
     */
    protected function _db_urls_insert(array $bind, bool $is_update = false)
    {
        if ($is_update && $bind['lastmod']) {
            manage::db_site()->table($this->_table_sitemap_urls)
                ->duplicate(['lastmod' => (int)$bind['lastmod']])
                ->insert($bind);
        } else {
            manage::db_site()->table($this->_table_sitemap_urls)
                ->option(' IGNORE ')
                ->insert($bind);
        }
    }

    /**
     * @param array $urls
     * @param int $type
     */
    protected function _db_push_insert(array $urls, int $type, int $Ymd = 0)
    {
        $Ymd = $Ymd ? $Ymd : date('Ymd');
        manage::db_site()->query("INSERT INTO {$this->_table_sitemap_push} (`url_id`, `Ymd`, `target_id`) SELECT `url_id`,{$Ymd},{$type} FROM {$this->_table_sitemap_urls} WHERE `loc` in ( ? );", $urls)->affected();
    }

    /**
     * @param $api
     * @param $urls
     * @return mixed
     */
    protected function _push_curl($api, $urls)
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
     * @param array $urls_domain
     * @return mixed
     */
    abstract protected function _push_api(array $urls_domain);

    /**
     * @param array $rs
     * @return array [$urls_path, $urls_domain]
     */
    abstract protected function _urls(array $rs);
}