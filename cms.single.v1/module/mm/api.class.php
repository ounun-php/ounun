<?php
namespace module\mm;

class api extends \module\base_api
{
    /**
     * 广告
     * @param $mod array
     */
    public function m($mod)
    {
        echo 'var m_gcom='.json_encode(\ads::m ,JSON_UNESCAPED_UNICODE).";\n";
        //echo 'function adwrite(mode,size){ var show = false; var str = \'mode:\'+mode+\' size:\'+size; if(m_gcom && m_gcom[mode]) { show = true;  str = m_gcom[mode];} document.write(str);}';
        echo 'function adwrite(mode,position,size) { var show = false; var str = "mode:"+mode+" position:"+position+" size:"+size; if(m_gcom && m_gcom[mode] && m_gcom[mode][position]) { show = true; str = m_gcom[mode][position]; } document.write(str); }';
    }

    /**
     * 广告
     * @param $mod array
     */
    public function times($mod)
    {
        $pic_id = (int)$mod[1];

        $this->init_page("/api/times/{$pic_id}.js",false,false);


        $this->_db_v->active();
        $this->_db_v->add('`pic_data`',['pic_times'=>1],' `pic_id` = ? ',$pic_id);

        $rs     = $this->_db_v->row('SELECT `pic_times` FROM  `pic_data` WHERE `pic_id` = ? LIMIT 0 , 1;',$pic_id);
        echo "try{details_pic_times_update({$pic_id},{$rs['pic_times']});}catch(err){}";
    }


    /**
     * @param $mod
     */
    public function mobile_app($mod)
    {
        if(!$_GET['ref'])
        {
            $ref = $_SERVER['HTTP_REFERER']?$_SERVER['HTTP_REFERER']:'i';
            \ounun::go_url('/api/mobile_app/?ref='.urlencode($ref));
        }

        $this->init_page("/api/mobile_app/",false,true,true,'api.383434.com',0,false);
        require $this->require_file_g('ads/mobile.html.php');
    }

    /**
     * @param $mod
     */
    public function mobile_go($mod)
    {
        $ref       = $_GET['ref'];
        $site_host = explode('/',$ref);
        $site_host = $site_host[2]?$site_host[2]:'';

        // http://47.98.158.95:9475/jiaoyou/login.php?b=a6nmoa9ofi1w7cr037ytt5pw9&a=16286
        // $ios      = 'http://t.cn/RDecTzG';//'http://t.cn/RegaGVQ';
        // $android  = 'http://t.cn/RDecTzG';//'http://t.cn/RegaGVQ';

        // print_r($GLOBALS['_scfg']['db']);

        $rurl     = substr(md5(time()),0,6);

        $ios      = 'https://wap.erldoc.com/xinggan/10058_2.html';//'https://'.$rurl.'.luvbr.com:5656/?mb=2&c=yiping01';// 'http://www.gumei88.com/spread/iosspread.php?channel=i701029';//'http://t.cn/RegaGVQ';
        $android  = 'https://wap.erldoc.com/xinggan/9840.html';//'https://'.$rurl.'.luvbr.com:5656/az/?c=yiping01';//'http://gdzmn.gyjffd.cn/miminew.php?id=13625';//'http://t.cn/RkQwFHW';//'http://t.cn/RkHnBYE';//'http://t.cn/RegaGVQ';

        $pc       = 'https://mm.erldoc.com/?f=ads';

        // $db    = self::db('core');
        $table    = '`z_ads_click`';
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

        if(strpos($_SERVER['HTTP_USER_AGENT'], 'iPhone')||strpos($_SERVER['HTTP_USER_AGENT'], 'iPad'))
        {
            $url_go         = $ios;
            $bind['ads_id'] = 'ht9989_h5_ios';
            $bind['go']     = $url_go;
        }else if(strpos($_SERVER['HTTP_USER_AGENT'], 'Android'))
        {
            //            $h    = (int)date('H');
            //            if($h > 10 && $h  < 18 )
            //            if(0 && $h > -1 )
            //            {
            //                $url_go         = $android;
            //                $bind['ads_id'] = 'ht9989_app_Android';
            //            }else
            //            {
            //                $url_go         = $ios;
            //                $bind['ads_id'] = 'ht9989_h5_Android';
            //            }
            $url_go         = $android;
            $bind['ads_id'] = 'ht9989_app_Android';
            $bind['go']     = $url_go;
        }else
        {
            $url_go         = $pc;
            $bind['ads_id'] = 'pc';
            $bind['go']     = $url_go;
        }

        // print_r(['$this->_db_v'=>$this->_db_v]);
        $this->_db_v = self::db(\ounun_scfg::$app);
        $this->_db_v->insert($table,$bind);
        // print_r($bind);
        \ounun::go_url($url_go);
    }
}