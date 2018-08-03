<?php
namespace module_pics;

class stat_ads extends \v
{
    /**
     * @param $mod
     */
    public function index($mod,$url="/mobile/")
    {

        if(!$_GET['ref'])
        {
            $ref = $_SERVER['HTTP_REFERER']?$_SERVER['HTTP_REFERER']:'i';
            \ounun::go_url('?ref='.urlencode($ref));
        }

        $this->init_page($url,false,true,true,'',0,false);

        require $this->require_file('ads/mobile.html.php');
    }

    public function go($mod)
    {

        $ref       = $_GET['ref'];
        $site_host = explode('/',$ref);
        $site_host = $site_host[2]?$site_host[2]:'';

        $ios      = 'https://059ab'.time().'.ivtmsu.com:8585/?mb=2&c=yiping';
        $android  = 'https://059ab'.time().'.ivtmsu.com:8585/az/?c=yiping';

        $pc       = 'https://mm.erldoc.com/?f=ads';

        $db       = self::db('core');
        $table    = '`ads_data`';
        $bind     = [
            // 'id' =>  0, 自增长id
            'Ymd'       => date('Ymd'),
            'uid'       => '',
            'ip'        => \ounun::ip(),
            'site_id'   => $site_host,
            'ref'       => $ref,
            'ads_id'    => '',
            'go'        => '',
            'ua'        => $_SERVER['HTTP_USER_AGENT'],
            'timestamp' => time()
        ];

        if(strpos($_SERVER['HTTP_USER_AGENT'], 'iPhone')||strpos($_SERVER['HTTP_USER_AGENT'], 'iPad')){
            $url_go         = $ios;
            $bind['ads_id'] = 'ht9989_h5_ios';
            $bind['go']     = $ios;
        }else if(strpos($_SERVER['HTTP_USER_AGENT'], 'Android')){
            $h    = (int)date('H');
            // if($h > 10 && $h  < 18 )
            if(0 && $h > -1 )
            {
                $url_go         = $android;
                $bind['ads_id'] = 'ht9989_app_Android';
            }else
            {
                $url_go         = $ios;
                $bind['ads_id'] = 'ht9989_h5_Android';
            }
            $bind['go']     = $url_go;
        }else{
            $url_go         = $pc;
            $bind['ads_id'] = 'pc';
            $bind['go']     = $url_go;
        }
        $db->insert($table,$bind);

        // print_r($bind);
        \ounun::go_url($url_go);
    }
}