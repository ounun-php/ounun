<?php
namespace app\adm\cmd;

/**
 * /usr/bin/php /Users/dreamxyp/Transcend/www/com.ygcms.mm.2015/index.php zrun_back,run_1m    //   每分钟调一次    **:*1 或 **:*0
 * /usr/bin/php /Users/dreamxyp/Transcend/www/com.ygcms.mm.2015/index.php zrun_back,run_5m    // 每五分钟调一次    **:*5 或 **:*0
 * /usr/bin/php /Users/dreamxyp/Transcend/www/com.ygcms.mm.2015/index.php zrun_back,run_10m   // 每十分钟调一次    **:*5 或 **:*0
 * /usr/bin/php /Users/dreamxyp/Transcend/www/com.ygcms.mm.2015/index.php zrun_back,run_1h    // 每一个小时调一次  **:59
 * /usr/bin/php /Users/dreamxyp/Transcend/www/com.ygcms.mm.2015/index.php zrun_back,run_4h    // 每四小时调一次    *4:58
 * /usr/bin/php /Users/dreamxyp/Transcend/www/com.ygcms.mm.2015/index.php zrun_back,run_12h   // 每12小时调一次    03:57  15:57
 * /usr/bin/php /Users/dreamxyp/Transcend/www/com.ygcms.mm.2015/index.php zrun_back,run_1d    // 每天23：59：00点调一次
 * /usr/bin/php /Users/dreamxyp/Transcend/www/com.ygcms.mm.2015/index.php zrun_back,run_1w36  // 每期三，六调用一次
 * /usr/bin/php /Users/dreamxyp/Transcend/www/com.ygcms.mm.2015/index.php zrun_cmd,getblock
 *
 * *\/5 *    * * * (/Users/dreamxyp/Transcend/www/com.ygcms.mm.2015/index.php zrun_back,Run5M  >> /data/xbsglogs/zrun_ZRunBack_Run5M.log 2>&1)
 * 59  *    * * * (/Users/dreamxyp/Transcend/www/com.ygcms.mm.2015/index.php zrun_back,Run1H  >> /data/xbsglogs/zrun_ZRunBack_Run1H.log 2>&1)
 * 58  *\/4  * * * (/Users/dreamxyp/Transcend/www/com.ygcms.mm.2015/index.php zrun_back,Run4H  >> /data/xbsglogs/zrun_ZRunBack_Run4H.log 2>&1)
 * 57  *\/12 * * * (/Users/dreamxyp/Transcend/www/com.ygcms.mm.2015/index.php zrun_back,Run12H >> /data/xbsglogs/zrun_ZRunBack_Run12H.log 2>&1)
 * 59  23   * * * (/Users/dreamxyp/Transcend/www/com.ygcms.mm.2015/index.php zrun_back,Run1D  >> /data/xbsglogs/zrun_ZRunBack_Run1D.log 2>&1)
 *
 * Class zrun_cmd
 * @package module
 */

use extend\oss;
use ounun\api_sdk\com_showapi;

class coll extends \ounun\cmd\cmd
{
    /**
     * zrun_cmd constructor.
     * @param $mod
     */
    public function __construct($mod)
    {
        $this->_db_zrun = self::db(\ounun\config::$app_name);
        parent::__construct($mod);
    }

    /**
     * php ~/Transcend/www.cms/app.adm/index.php zrun_cmd,oss_mm,hzmm,131
     *
     * php /www/wwwroot/moko8.com/app.adm/index.php zrun_cmd,oss_mm,hzmm,131
     * php /www/wwwroot/moko8.com/app.adm/index.php zrun_cmd,oss_mm,hzmm,6mm
     * php /www/wwwroot/moko8.com/app.adm/index.php zrun_cmd,oss_mm,hzmm,99mm
     * php /www/wwwroot/moko8.com/app.adm/index.php zrun_cmd,oss_mm,hzmm,jpg
     * php /www/wwwroot/moko8.com/app.adm/index.php zrun_cmd,oss_mm,wgyw,yw
     * @param $mod
     */
    public function oss_mm($mod)
    {
        print_r($mod);
        $list = [
            'hzmm_6mm'  => ['src'=>'/data/ossfs/6mm/'       , 'taget'=>'/tmp/ossfs_io3/6mm/' ],
            'hzmm_99mm' => ['src'=>'/data/ossfs/99mm/'      , 'taget'=>'/tmp/ossfs_io3/99mm/'],
            'hzmm_jpg'  => ['src'=>'/data/ossfs/jpg/'       , 'taget'=>'/tmp/ossfs_io3/jpg/' ],
            'hzmm_131'  => ['src'=>'/data/ossfs/131/'       , 'taget'=>'/tmp/ossfs_io3/131/' ],
            'wgyw_yw'   => ['src'=>'/data/ossfs_wgyw/yw/'   , 'taget'=>'/tmp/ossfs_io3/yw/'  ],
        ];
        if('hzmm' == $mod[1] && '131' == $mod[2]){
            $list2  = [$list["{$mod[1]}_{$mod[2]}"]];
        }elseif ('hzmm' == $mod[1] && '6mm' == $mod[2])
        {
            $list2  = [$list["{$mod[1]}_{$mod[2]}"]];
        }elseif ('hzmm' == $mod[1] && '99mm' == $mod[2])
        {
            $list2  = [$list["{$mod[1]}_{$mod[2]}"]];
        }elseif ('hzmm' == $mod[1] && 'jpg' == $mod[2])
        {
            $list2  = [$list["{$mod[1]}_{$mod[2]}"]];
        }elseif ('wgyw' == $mod[1] && 'yw' == $mod[2])
        {
            $list2  = [$list["{$mod[1]}_{$mod[2]}"]];
        }else
        {
            $list2  = array_values($list);
        }
        foreach($list2 as $v2)
        {
            $src_dir   = $v2['src'];
            $taget_dir = $v2['taget'];
            // print_r($v2);
            // echo "\n";
            if($src_dir && $taget_dir)
            {
                $oss =  new oss($src_dir,$taget_dir);
                $oss->scan('');
            }
        }
    }

    /**
     * php ~/Transcend/www.cms/app.adm/index.php zrun_cmd,oss_rename
     * @param $mod
     */
    public function oss_rename($mod)
    {
        print_r($mod);
        oss::rename('','/Users/dreamxyp/Transcend/fcash.cash/gitian.sigs.fcash/');
    }

    /**
     *  php /www/wwwroot/moko8.com/app.adm/index.php zrun_cmd,oss_75,dtxt_75_files
     */
    public function oss_75($mod)
    {
        print_r($mod);

        $table   = $mod[1];
        $order   = $mod[2]?'DESC':'ASC';

        $tables  = ['dtxt_75_files'];
        if(!in_array($table,$tables))
        {
            exit("\$table:{$table}\n");
        }
        $root     = '/tmp/ossfs_io3/';
        $id_start = 0;
        // exit();
        do
        {
            $where_str   = $mod[2]?' ':" and `id` > {$id_start} ";
            $rs          = $this->_db_zrun->data_array("SELECT * FROM `dtxt_75_files` WHERE `is_wget` = 0 {$where_str} ORDER BY `id` {$order} limit 0,50;");
            foreach ($rs as $v)
            {
                $id_start = (int)($v['id']?$v['id']:$id_start);
                $wget     = $v['src_url'];
                $file     = "{$root}{$v['dir']}/{$v['file']}";
                $is_file  = file_exists($file)?'1':'0';
                $is_update= false;
                if($is_file)
                {
                    $is_s    = filesize($file);
                    $is_s2   = str_pad($is_s,10);
                    if( $is_s > 1024)
                    {
                        $is_update = true;
                    }else
                    {
                        \plugins\curl\http::file_get_put($wget,$file);
                        $is_file  = file_exists($file)?'1':'0';
                        $is_s     = '1'==$is_file?filesize($file):0;
                        if($is_file && $is_s > 1024)
                        {
                            $is_update = true;
                        }
                    }
                }else
                {
                    \plugins\curl\http::file_get_put($wget,$file);
                    $is_file  = file_exists($file)?'1':'0';
                    $is_s     = '1'==$is_file?filesize($file):0;
                    if($is_file && $is_s > 1024)
                    {
                        $is_update = true;
                    }
                }

                if($is_update)
                {
                    $bind = ['is_wget'=>1];
                    $this->_db_zrun->update('`dtxt_75_files`',$bind," `id` = :id ",$v);
                    echo "i {$v['id']}:{$is_file} s:{$is_s2} f:{$v['file']} url:{$wget}\n";
                }else
                {
                    $bind = ['is_wget'=>-9];
                    $this->_db_zrun->update('`dtxt_75_files`',$bind," `id` = :id ",$v);
                    echo "- i {$v['id']}:{$is_file} f:{$v['file']} url:{$wget}  ------------- {$file}  \n";
                }
            }

        }while($rs);

    }

    /**
     * php ~/Transcend/www.cms.adm/app.adm/index.php zrun_cmd,test
     * @param $mod
     */
    public function test($mod)
    {
        print_r($mod);
        //
        $crontabs = [
            "* * * * *",
            "0 3 * * *",
            "15,30 3 * * * *",
            "15-30 3 * * *",
            "0-30/10 3 * * *",
            "0-10,50-59/2 3 * * *"
        ];
        foreach ($crontabs as $v)
        {
            $c    = new \plugins\crontab($v);
            $time = time();
            $rs = $c->check(time());
            echo "\$time:{$time}----\n";
            print_r(['$rs'=>$rs,'c'=>$c->cron()[1]]);
        }

        $rs =  com_showapi::tag("性感秘书尤妮丝黑丝制服让人想入非非");
        print_r(['$rs'=>$rs]);
    }
    // ------------------------------------------------------------------------------------
}
