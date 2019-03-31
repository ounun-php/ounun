<?php
namespace ounun\cmd\task\site_base;

use ounun\cmd\task\libs\com_baidu;
use ounun\cmd\task\manage;
use ounun\cmd\task\task_base;
use ounun\config;
use ounun\pdo;

abstract class _site extends task_base
{
    /** @var com_baidu */
    protected $_baidu_sdk;

    protected $_table_sitemap       = ' `z_sitemap` ';
    protected $_table_sitemap_push  = ' `z_sitemap_push` ';

    /** @var int 单次提交数据 */
    protected $_push_step    = 1000;

    protected $_url_root_pc  = '';
    protected $_url_root_mip = '';
    protected $_url_root_wap = '';

    protected $_domain_pc    = '';
    protected $_domain_mip   = '';
    protected $_domain_wap   = '';

    protected $_token_site   = '';
    protected $_token_xzh    = '';
    protected $_appid_xzh    = '';


    /** @var pdo 网站数据 */
    protected $_db_site;

    public function config_set(string $db_tag = '', array $db_config = [])
    {
        if($db_tag) {
            $this->_db_site = pdo::instance($db_tag,$db_config);
        }

        $config      = $this->config();
        if($config['domain_pc']){
            $this->_domain_pc    = $config['domain_pc'];
            $this->_url_root_pc  = "https://{$this->_domain_pc}";
        }
        if($config['domain_mip']){
            $this->_domain_mip   = $config['domain_mip'];
            $this->_url_root_mip = "https://{$this->_domain_mip}";
        }
        if($config['domain_mip']){
            $this->_domain_wap   = $config['domain_wap'];
            $this->_url_root_wap = "https://{$this->_domain_wap}";
        }
        if($config['token_site']){
            $this->_token_site   = $config['token_site'];
        }
        if($config['token_xzh']){
            $this->_token_xzh    = $config['token_xzh'];
        }
        if($config['appid_xzh']){
            $this->_appid_xzh    = $config['appid_xzh'];
        }
    }


    /**
     * 定时  数据接口提交
     * @param bool $is_today  false :历史   true  :当天
     */
    public function do_push($is_today = false)
    {
        $this->do_push_pc($is_today);
        $this->do_push_mip($is_today);
        $this->do_push_wap($is_today);
        $this->do_push_xzh_realtime($is_today);
        $this->do_push_xzh_batch($is_today);
    }

    /**
     * 定时  数据接口提交 pc
     * @param bool $is_today  false :历史    true  :当天
     */
    public function do_push_pc($is_today = false)
    {
        $this->_push_step = com_baidu::max_push_step;
        do{
            $do = $this->_do_push(com_baidu::type_baidu_pc,$is_today);
        }while($do);
    }

    /**
     * 定时  数据接口提交 mip
     * @param bool $is_today  false :历史   true  :当天
     */
    public function do_push_mip($is_today = false)
    {
        $this->_push_step = com_baidu::max_push_step;
        do{
            $do = $this->_do_push(com_baidu::type_baidu_mip,$is_today);
        }while($do);
    }

    /**
     * 定时  数据接口提交 mip
     * @param bool $is_today  false :历史   true  :当天
     */
    public function do_push_wap($is_today = false)
    {
        $this->_push_step = com_baidu::max_push_step;
        do{
            $do = $this->_do_push(com_baidu::type_baidu_wap,$is_today);
        }while($do);
    }

    /**
     * 定时  数据接口提交 mip
     * @param bool $is_today  false :历史  true  :当天
     */
    public function do_push_xzh_realtime($is_today = false)
    {
        $this->_push_step = com_baidu::max_push_xzh_doday;
        do{
            $do = $this->_do_push(com_baidu::type_baidu_xzh_realtime,$is_today);
        }while($do);
    }

    /**
     * 定时  数据接口提交 mip
     * @param bool $is_today  false :历史   true  :当天
     */
    public function do_push_xzh_batch($is_today = false)
    {
        $this->_push_step = com_baidu::max_push_step;
        do{
            $do = $this->_do_push(com_baidu::type_baidu_xzh_batch,$is_today);
        }while($do);
    }

    /**
     * @param $type
     * @param $is_today
     * @return bool
     */
    protected function _do_push($type,$is_today)
    {
        $max  = com_baidu::push_max[$type];
        $Ymd  = date('Ymd');
        $time = time();
        if($max)
        {
            $cc   = $this->_db_site->query("SELECT COUNT(`url_id`) as `cc` FROM {$this->_table_sitemap_push} WHERE `Ymd` = :Ymd and `target_id` = :target_id ;",['Ymd'=>$Ymd,'target_id'=>$type])->column_one();
            $cc   = (int)$cc['cc'];
            // echo $db->sql()."\n";
            $this->msg("COUNT(`url_id`) as cc:{$cc} ".com_baidu::type[$type]);
            if($cc < $max)
            {
                $step_cc0  = $max - $cc;
                $step_cc   = $step_cc0 > $this->_push_step ? $this->_push_step : $step_cc0 ;
                $Ymd_start = date('Ymd',$time-com_baidu::push_rate);
                $where_xzh = '';
                if($type == com_baidu::type_baidu_xzh_realtime) {
                    $where_xzh = " `xzh` = 1 and ";
                }
                if($is_today) {
                    $today_time  = $time - 3600 * 24;
                    $bind        = ['Ymd'=>$Ymd_start,'target_id'=>$type,'lastmod'=>$today_time];
                    $rs          = $this->_db_site->query("SELECT * FROM {$this->_table_sitemap} WHERE {$where_xzh} `lastmod` > :lastmod and `url_id` NOT in ( SELECT `url_id` FROM {$this->_table_sitemap_push} WHERE `Ymd` >= :Ymd and `target_id` = :target_id ) ORDER BY `lastmod` DESC LIMIT 0,".$step_cc.";",$bind)->column_all();
                }else {
                    $bind        = ['Ymd'=>$Ymd_start,'target_id'=>$type];
                    $rs          = $this->_db_site->query("SELECT * FROM {$this->_table_sitemap} WHERE {$where_xzh} `url_id` NOT in ( SELECT `url_id` FROM {$this->_table_sitemap_push} WHERE `Ymd` >= :Ymd and `target_id` = :target_id ) ORDER BY `lastmod` DESC LIMIT 0,".$step_cc.";",$bind)->column_all();
                }
                if($rs) {
                    $url_root          = $this->_url_root_pc;
                    if($type == com_baidu::type_baidu_pc) {
                        $url_root      = $this->_url_root_pc;
                    }elseif ($type == com_baidu::type_baidu_mip) {
                        $url_root      = $this->_url_root_mip;
                    }elseif ($type == com_baidu::type_baidu_wap) {
                        $url_root      = $this->_url_root_wap;
                    }elseif ($type == com_baidu::type_baidu_xzh_realtime) {
                        $url_root      = $this->_url_root_mip;
                    }elseif ($type == com_baidu::type_baidu_xzh_batch) {
                        $url_root      = $this->_url_root_mip;
                    }
                    // -------------------------------------
                    $urls              = [];
                    $urls_domain       = [];
                    foreach ($rs as $v) {
                        $urls[]        = $v['loc'];
                        $urls_domain[] = $url_root.$v['loc'];
                    }
                    if($urls_domain) {
                        if($type == com_baidu::type_baidu_pc) {
                            $rs2            = $this->push_pc($urls_domain);
                            $success        = (int)$rs2['success'];
                            $remain         = (int)$rs2['remain'];
                            $max_push_step  = com_baidu::max_push_step;
                        }elseif($type == com_baidu::type_baidu_wap)  {
                            $rs2            = $this->push_wap($urls_domain);
                            $success        = (int)$rs2['success'];
                            $remain         = (int)$rs2['remain'];
                            $max_push_step  = com_baidu::max_push_step;
                        }elseif ($type == com_baidu::type_baidu_mip) {
                            $rs2            = $this->push_mip($urls_domain);
                            $success        = (int)$rs2['success_mip'];
                            $remain         = (int)$rs2['remain_mip'];
                            $max_push_step  = com_baidu::max_push_step;
                        }elseif ($type == com_baidu::type_baidu_xzh_realtime) {
                            $rs2            = $this->push_xzh_realtime($urls_domain);
                            $success        = (int)$rs2['success_realtime'];
                            $remain         = (int)$rs2['remain_realtime'];
                            $max_push_step  = com_baidu::max_push_xzh_doday;
                        }elseif ($type == com_baidu::type_baidu_xzh_batch) {
                            $rs2            = $this->push_xzh_batch($urls_domain);
                            $success        = (int)$rs2['success_batch'];
                            $remain         = (int)$rs2['remain_batch'];
                            $max_push_step  = com_baidu::max_push_step;
                        }

                        $this->msg("cc:".count($urls_domain)." type:{$type} success:{$success} push_step:{$this->_push_step}");
                        $this->_push_step  = $remain > $max_push_step ? $max_push_step : $remain;
                        if($success == count($urls_domain)) {
                            $this->db_sitemap_push($urls,$type);
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
     * @param int    $xzh
     * @param string $mod
     * @param string $changefreq  "always", "hourly", "daily", "weekly", "monthly", "yearly"
     * @param float  $weight
     * @return array
     */
    public function sitemap_data(string $url,string $mod = 'page',string $changefreq = com_baidu::changefreq_daily, int $xzh = 1,float $weight = 0.95)
    {
        $time = time();
        return  [ 'loc' =>$url,'xzh'=>$xzh,'mod'=>$mod, 'time_add'=> $time,'changefreq' => $changefreq, 'lastmod' => $time, 'weight' => $weight , ];
    }

    /**
     * @param array $bind
     * @param bool $is_update
     */
    public function db_sitemap(array $bind, bool $is_update = false)
    {
        if($is_update && $bind['lastmod'])
        {
            $this->_db_site->table($this->_table_sitemap)->duplicate(['lastmod'=> pdo::Update_Update])->insert($bind);
        }else
        {
            $this->_db_site->table($this->_table_sitemap)->option('IGNORE')->insert($bind);
        }
    }

    /**
     * @param array $urls
     * @param int $type
     */
    public function db_sitemap_push(array $urls, int $type,int $Ymd = 0)
    {
        $Ymd  = $Ymd ? $Ymd : date('Ymd');
        $this->_db_site->query("INSERT INTO {$this->_table_sitemap_push} (`url_id`, `Ymd`, `target_id`) SELECT `url_id`,{$Ymd},{$type} FROM {$this->_table_sitemap} WHERE `loc` in ( ? );",$urls)->affected();
    }


    public function push_pc(array $urls)
    {
        $api = str_replace(['{$site}','{$token}'],[$this->_url_root_pc,$this->_token_site],com_baidu::api_baidu_pc);
        return $this->_push($api,$urls);
    }

    public function push_mip(array $urls)
    {
        $api = str_replace(['{$site}','{$token}'],[$this->_url_root_mip,$this->_token_site],com_baidu::api_baidu_mip);
        return $this->_push($api,$urls);
    }

    public function push_wap(array $urls)
    {
        $api = str_replace(['{$site}','{$token}'],[$this->_url_root_wap,$this->_token_site],com_baidu::api_baidu_wap);
        return $this->_push($api,$urls);
    }

    public function push_xzh_batch(array $urls)
    {
        $api = str_replace(['{$appid}','{$token}'],[$this->_appid_xzh,$this->_token_xzh],com_baidu::api_xzh_batch);
        return $this->_push($api,$urls);
    }

    public function push_xzh_realtime(array $urls)
    {
        $api = str_replace(['{$appid}','{$token}'],[$this->_appid_xzh,$this->_token_xzh],com_baidu::api_xzh_realtime);
        return $this->_push($api,$urls);
    }

    protected function _push($api,$urls)
    {
        // $this->msg("urls:".count($urls)." api:{$api}");
        $ch      = curl_init();
        $options = [
            CURLOPT_URL            => $api,
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS     => implode("\n", $urls),
            CURLOPT_HTTPHEADER     => ['Content-Type: text/plain'],
        ];
        curl_setopt_array($ch, $options);
        $result = curl_exec($ch);
        // $this->msg($result .$urls[0]);
        $this->msg($result );
        return json_decode($result,true);
    }

    /**
     * 网站地址
     * @param $mod array
     */
    public function maps_index($url_root='sitemap/list/',string $host='')
    {

        $total      = $this->_db_site->query("SELECT count(`url_id`) as `cc` FROM {$this->_table_sitemap};")->column_one();
        $total      = (int)$total['cc'];
        $total_page = ceil($total   / com_baidu::max_sitemaps_page );

        $url_root2  = $this->_maps_url($host);
        $urls       = [];
        $date       = date('Y-m-d',time());
        for ($page =1;$page <= $total_page;$page++)
        {
            $urls[] = ['lastmod'=>$date,'loc'=> "{$url_root2}/{$url_root}{$page}.xml"];
        }
        $xml        = $this->_maps_index($urls);
        return $xml;
    }

    /**
     * 特别URL 路由
     */
    public function maps_page(int $page=1,string $host='')
    {
        $page          = $page < 1?1:$page;
        $start         = ($page - 1) * com_baidu::max_sitemaps_page;
        $rows          = com_baidu::max_sitemaps_page;
        $rs            = $this->_db_site->query("SELECT * FROM {$this->_table_sitemap} ORDER BY `lastmod` DESC ,`url_id` ASC limit {$start},{$rows};")->column_all();
        // echo $this->_db->sql()."<br />";
        $url_root      = $this->_maps_url($host);
        $urls          = [];
        foreach ($rs as $v)
        {
            $urls[]    =  [
                'loc'        => $url_root.$v['loc'],
                'priority'   => 0.0+$v['weight'],
                'lastmod'    => date('Y-m-d',$v['lastmod']),
                'changefreq' => $v['changefreq']
            ];
        }
        // print_r($urls);
        $xml     = $this->_maps_page($urls);
        if($this->_domain_mip == $host && $this->_domain_wap == $host)
        {
            $xml = str_replace('</loc>','</loc><mobile:mobile type="mobile" />',$xml);
        }
        return $xml;
    }

    /**
     * @param string $host
     * @return string
     */
    protected function _maps_url(string $host='')
    {
        switch ($host)
        {
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
     * @param $sitemaps
     * @return string
     */
    protected function _maps_index($sitemaps)
    {
        return '<?xml version="1.0" encoding="utf-8"?'.'>'."\n"
            .'<sitemapindex>'."\n"
            . \ounun\tool\data::array2xml($sitemaps,'sitemap').'</sitemapindex>';
    }

    /**
     * 生成sitemap
     * @param $data [array('loc'=>'http://www.383434.com','priority'=>'1.00', 'lastmod'=>'2015-03-23','changefreq'=>'daily')]
     * @return string
     */
    protected function _maps_page($urls_data)
    {
        $urls = [];
        foreach ($urls_data as $v)
        {
            $urls[] = "<url><loc>{$v['loc']}</loc><lastmod>{$v['lastmod']}</lastmod><changefreq>{$v['changefreq']}</changefreq><priority>{$v['priority']}</priority></url>";
        }
        return '<?xml version="1.0" encoding="UTF-8"?'.'>'."\n"
            .'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:mobile="http://www.baidu.com/schemas/sitemap-mobile/1/">'."\n"
            .implode('',$urls)
            .'</urlset>';
    }

    /**
     * 日志记录
     * @param string $msg
     * @param int $state
     * @param int $time
     */
    protected function msg(string $msg,int $state = 0,int $time=-1)
    {
        if($msg) {
            $time  = $time  == -1 ? time() : $time;
            manage::logs()->data($state,$time,$msg);
        }
    }

    /**
     * @return array
     */
    protected function config()
    {
        return [
            'domain_pc'  => explode('/',config::$url_www)[2],
            'domain_mip' => explode('/',config::$url_mip)[2],
            'domain_wap' => explode('/',config::$url_wap)[2],
            'token_xzh'  => config::$global['baidu']['xzh_token'],// Const_Baidu_Xzh_Token ,
            'token_site' => config::$global['baidu']['token'],// Const_Baidu_Token ,
            'appid_xzh'  => config::$global['baidu']['xzh_site_id'],//  Const_Baidu_Xzh_SiteId ,
        ];
    }
}