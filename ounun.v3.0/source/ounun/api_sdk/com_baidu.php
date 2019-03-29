<?php
namespace ounun\api_sdk;


use ounun\logs;
use ounun\pdo;

class com_baidu
{
    /** Mip Baidu API */
    const api_baidu_mip      = 'http://data.zz.baidu.com/urls?site={$site}&token={$token}&type=mip';
    /** PC  Baidu API */
    const api_baidu_pc       = 'http://data.zz.baidu.com/urls?site={$site}&token={$token}';
    /** Wap Baidu API */
    const api_baidu_wap      = 'http://data.zz.baidu.com/urls?site={$site}&token={$token}';

    /** 历史内容接口 - 熊掌号 API  */
    const api_xzh_batch      = 'http://data.zz.baidu.com/urls?appid={$appid}&token={$token}&type=batch';
    /** 新增内容接口 - 熊掌号 API  */
    const api_xzh_realtime   = 'http://data.zz.baidu.com/urls?appid={$appid}&token={$token}&type=realtime';


    const type_baidu_mip           = 1;
    const type_baidu_pc            = 2;
    const type_baidu_wap           = 5;
    const type_baidu_xzh_realtime  = 3;
    const type_baidu_xzh_batch     = 4;

    const type = [
        self::type_baidu_mip          => '百度[MIP]',
        self::type_baidu_pc           => '百度[PC]',
        self::type_baidu_wap          => '百度[WAP]',
        self::type_baidu_xzh_realtime => '熊掌号[实时]',
        self::type_baidu_xzh_batch    => '熊掌号[历史]',
    ];

    /** 全部提交频率 45天 */
    const push_rate           = 3888000;  // 3600 * 24 * 45

    /** 站点 每次  每次提交数量 */
    const max_push_step       = 1000;
    /** 熊掌号 - 当天 - 每次提交数量  */
    const max_push_xzh_doday  = 10;
    /** 网址地图 单页最大数量 */
    const max_sitemaps_page   = 5000;

    /** 接口最大提交量 每天 */
    const push_max    = [
        self::type_baidu_mip           =>   10000,
        self::type_baidu_pc            => 5000000,
        self::type_baidu_wap           => 5000000,
        self::type_baidu_xzh_realtime  =>      10,
        self::type_baidu_xzh_batch     => 5000000,
    ];

    /** "always", "hourly", "daily", "weekly", "monthly", "yearly" */
    const changefreq_always   = "always";
    const changefreq_hourly   = "hourly";
    const changefreq_daily    = "daily";
    const changefreq_weekly   = "weekly";
    const changefreq_monthly  = "monthly";
    const changefreq_yearly   = "yearly";


    protected $_db_sitemap       = ' `z_sitemap` ';
    protected $_db_sitemap_push  = ' `z_sitemap_push` ';

    /** @var \ounun\pdo */
    protected $_db;
    /** @var \ounun\logs  */
    protected $_logs;
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


    /**
     * com_baidu constructor.
     * @param \ounun\pdo       $db
     * @param \ounun\logs|null $logs
     * @param array $config
     */
    public function __construct(\ounun\pdo $db,\ounun\logs $logs = null)
    {
        $this->_db   = $db;
        $this->_logs = $logs;
        $config      = $this->config();
        if($config['domain_pc'])
        {
            $this->_domain_pc    = $config['domain_pc'];
            $this->_url_root_pc  = "https://{$this->_domain_pc}";
        }
        if($config['domain_mip'])
        {
            $this->_domain_mip   = $config['domain_mip'];
            $this->_url_root_mip = "https://{$this->_domain_mip}";
        }
        if($config['domain_mip'])
        {
            $this->_domain_wap   = $config['domain_wap'];
            $this->_url_root_wap = "https://{$this->_domain_wap}";
        }
        if($config['token_site'])
        {
            $this->_token_site   = $config['token_site'];
        }
        if($config['token_xzh'])
        {
            $this->_token_xzh    = $config['token_xzh'];
        }
        if($config['appid_xzh'])
        {
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
        $this->_db->active();
        $this->_push_step = self::max_push_step;
        do{
            $do = $this->_do_push(self::type_baidu_pc,$is_today);
        }while($do);
    }

    /**
     * 定时  数据接口提交 mip
     * @param bool $is_today  false :历史   true  :当天
     */
    public function do_push_mip($is_today = false)
    {
        $this->_db->active();
        $this->_push_step = self::max_push_step;
        do{
            $do = $this->_do_push(self::type_baidu_mip,$is_today);
        }while($do);
    }

    /**
     * 定时  数据接口提交 mip
     * @param bool $is_today  false :历史   true  :当天
     */
    public function do_push_wap($is_today = false)
    {
        $this->_db->active();
        $this->_push_step = self::max_push_step;
        do{
            $do = $this->_do_push(self::type_baidu_wap,$is_today);
        }while($do);
    }

    /**
     * 定时  数据接口提交 mip
     * @param bool $is_today  false :历史  true  :当天
     */
    public function do_push_xzh_realtime($is_today = false)
    {
        $this->_db->active();
        $this->_push_step = self::max_push_xzh_doday;
        do{
            $do = $this->_do_push(self::type_baidu_xzh_realtime,$is_today);
        }while($do);
    }

    /**
     * 定时  数据接口提交 mip
     * @param bool $is_today  false :历史   true  :当天
     */
    public function do_push_xzh_batch($is_today = false)
    {
        $this->_db->active();
        $this->_push_step = self::max_push_step;
        do{
            $do = $this->_do_push(self::type_baidu_xzh_batch,$is_today);
        }while($do);
    }

    /**
     * @param $type
     * @param $is_today
     * @return bool
     */
    protected function _do_push($type,$is_today)
    {
        $max  = self::push_max[$type];
        $Ymd  = date('Ymd');
        $time = time();
        if($max)
        {
            $cc   = $this->_db->query("SELECT COUNT(`url_id`) as `cc` FROM {$this->_db_sitemap_push} WHERE `Ymd` = :Ymd and `target_id` = :target_id ;",['Ymd'=>$Ymd,'target_id'=>$type])->column_one();
            $cc   = (int)$cc['cc'];
            // echo $db->sql()."\n";
            $this->msg("COUNT(`url_id`) as cc:{$cc} ".self::type[$type]);
            if($cc < $max)
            {
                $step_cc0  = $max - $cc;
                $step_cc   = $step_cc0 > $this->_push_step ? $this->_push_step : $step_cc0 ;
                $Ymd_start = date('Ymd',$time-self::push_rate);
                $where_xzh = '';
                if($type == self::type_baidu_xzh_realtime)
                {
                    $where_xzh = " `xzh` = 1 and ";
                }
                if($is_today)
                {
                    $today_time  = $time - 3600 * 24;
                    $bind        = ['Ymd'=>$Ymd_start,'target_id'=>$type,'lastmod'=>$today_time];
                    $rs          = $this->_db->query("SELECT * FROM {$this->_db_sitemap} WHERE {$where_xzh} `lastmod` > :lastmod and `url_id` NOT in ( SELECT `url_id` FROM {$this->_db_sitemap_push} WHERE `Ymd` >= :Ymd and `target_id` = :target_id ) ORDER BY `lastmod` DESC LIMIT 0,".$step_cc.";",$bind)->column_all();
                }else
                {
                    $bind        = ['Ymd'=>$Ymd_start,'target_id'=>$type];
                    $rs          = $this->_db->query("SELECT * FROM {$this->_db_sitemap} WHERE {$where_xzh} `url_id` NOT in ( SELECT `url_id` FROM {$this->_db_sitemap_push} WHERE `Ymd` >= :Ymd and `target_id` = :target_id ) ORDER BY `lastmod` DESC LIMIT 0,".$step_cc.";",$bind)->column_all();
                }
                if($rs)
                {
                    $url_root          = $this->_url_root_pc;
                    if($type == self::type_baidu_pc)
                    {
                        $url_root      = $this->_url_root_pc;
                    }elseif ($type == self::type_baidu_mip)
                    {
                        $url_root      = $this->_url_root_mip;
                    }elseif ($type == self::type_baidu_wap)
                    {
                        $url_root      = $this->_url_root_wap;
                    }elseif ($type == self::type_baidu_xzh_realtime)
                    {
                        $url_root      = $this->_url_root_mip;
                    }elseif ($type == self::type_baidu_xzh_batch)
                    {
                        $url_root      = $this->_url_root_mip;
                    }
                    // -------------------------------------
                    $urls              = [];
                    $urls_domain       = [];
                    foreach ($rs as $v)
                    {
                        $urls[]        = $v['loc'];
                        $urls_domain[] = $url_root.$v['loc'];
                    }
                    if($urls_domain)
                    {
                        if($type == self::type_baidu_pc)
                        {
                            $rs2            = $this->push_pc($urls_domain);
                            $success        = (int)$rs2['success'];
                            $remain         = (int)$rs2['remain'];
                            $max_push_step  = self::max_push_step;
                        }elseif($type == self::type_baidu_wap)
                        {
                            $rs2            = $this->push_wap($urls_domain);
                            $success        = (int)$rs2['success'];
                            $remain         = (int)$rs2['remain'];
                            $max_push_step  = self::max_push_step;
                        }elseif ($type == self::type_baidu_mip)
                        {
                            $rs2            = $this->push_mip($urls_domain);
                            $success        = (int)$rs2['success_mip'];
                            $remain         = (int)$rs2['remain_mip'];
                            $max_push_step  = self::max_push_step;
                        }elseif ($type == self::type_baidu_xzh_realtime)
                        {
                            $rs2            = $this->push_xzh_realtime($urls_domain);
                            $success        = (int)$rs2['success_realtime'];
                            $remain         = (int)$rs2['remain_realtime'];
                            $max_push_step  = self::max_push_xzh_doday;
                        }elseif ($type == self::type_baidu_xzh_batch)
                        {
                            $rs2            = $this->push_xzh_batch($urls_domain);
                            $success        = (int)$rs2['success_batch'];
                            $remain         = (int)$rs2['remain_batch'];
                            $max_push_step  = self::max_push_step;
                        }

                        $this->msg("cc:".count($urls_domain)." type:{$type} success:{$success} push_step:{$this->_push_step}");
                        $this->_push_step  = $remain > $max_push_step ? $max_push_step : $remain;
                        if($success == count($urls_domain))
                        {
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
    public function sitemap_data(string $url,string $mod = 'page',string $changefreq = self::changefreq_daily, int $xzh = 1,float $weight = 0.95)
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
            $this->_db->table($this->_db_sitemap)->duplicate(['lastmod'=> pdo::Update_Update])->insert($bind);
        }else
        {
            $this->_db->table($this->_db_sitemap)->option('IGNORE')->insert($bind);
        }
    }

    /**
     * @param array $urls
     * @param int $type
     */
    public function db_sitemap_push(array $urls, int $type,int $Ymd = 0)
    {
        $Ymd  = $Ymd ? $Ymd : date('Ymd');
        $this->_db->query("INSERT INTO {$this->_db_sitemap_push} (`url_id`, `Ymd`, `target_id`) SELECT `url_id`,{$Ymd},{$type} FROM {$this->_db_sitemap} WHERE `loc` in ( ? );",$urls)->affected();
    }


    public function push_pc(array $urls)
    {
        $api = str_replace(['{$site}','{$token}'],[$this->_url_root_pc,$this->_token_site],self::api_baidu_pc);
        return $this->_push($api,$urls);
    }

    public function push_mip(array $urls)
    {
        $api = str_replace(['{$site}','{$token}'],[$this->_url_root_mip,$this->_token_site],self::api_baidu_mip);
        return $this->_push($api,$urls);
    }

    public function push_wap(array $urls)
    {
        $api = str_replace(['{$site}','{$token}'],[$this->_url_root_wap,$this->_token_site],self::api_baidu_wap);
        return $this->_push($api,$urls);
    }

    public function push_xzh_batch(array $urls)
    {
        $api = str_replace(['{$appid}','{$token}'],[$this->_appid_xzh,$this->_token_xzh],self::api_xzh_batch);
        return $this->_push($api,$urls);
    }

    public function push_xzh_realtime(array $urls)
    {
        $api = str_replace(['{$appid}','{$token}'],[$this->_appid_xzh,$this->_token_xzh],self::api_xzh_realtime);
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

        $total      = $this->_db->query("SELECT count(`url_id`) as `cc` FROM {$this->_db_sitemap};")->column_one();
        $total      = (int)$total['cc'];
        $total_page = ceil($total   / self::max_sitemaps_page );

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
        $start         = ($page - 1) * self::max_sitemaps_page;
        $rows          = self::max_sitemaps_page;
        $rs            = $this->_db->data_array("SELECT * FROM {$this->_db_sitemap} ORDER BY `lastmod` DESC ,`url_id` ASC limit {$start},{$rows};");
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
              .\ounun\xml::array2xml($sitemaps,'sitemap').'</sitemapindex>';
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
        if($msg && $this->_logs)
        {
            $time  = $time  == -1 ? time() : $time;
            $this->_logs->data($state,$time,$msg);
        }
    }

    /**
     * @return array|void
     */
    protected function config()
    {
        foreach (['Const_Url_Www','Const_Url_Mip','Const_Url_Wap','Const_Baidu_Xzh_Token','Const_Baidu_Token','Const_Baidu_Xzh_SiteId'] as $k)
        {
            if(!defined($k))
            {
                $msg = 'defined '.$k;
                if($this->_logs)
                {
                    $this->msg($msg,logs::state_fail);
                    return null;
                }else
                {
                    exit($msg);
                }
            }
        }

        return [
            'domain_pc'  => explode('/',Const_Url_Www)[2],
            'domain_mip' => explode('/',Const_Url_Mip)[2],
            'domain_wap' => explode('/',Const_Url_Wap)[2],
            'token_xzh'  => Const_Baidu_Xzh_Token ,
            'token_site' => Const_Baidu_Token ,
            'appid_xzh'  => Const_Baidu_Xzh_SiteId ,
        ];
    }
}