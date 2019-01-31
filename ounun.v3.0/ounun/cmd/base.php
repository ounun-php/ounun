<?php
namespace ounun\cmd;

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
use ounun\cmd\task\manage;

class base extends \ounun\base
{

    /** @var \ounun\mysqli DB */
    protected $_db_zrun;

    /**
     * php index.php zrun_cmd,crontab_step,5 adm   任务ID
     * @param $mod
     */
    public function crontab_step($mod)
    {
        print_r($mod);

        $task_id      = $mod[1];
        $paras        = array_slice($mod,2);
        // print_r(['$paras'=>$paras]);

        $task_manage  = new manage($this->_db_zrun);
        $task_manage->init($task_id);
        $task_manage->run_step($task_id,$paras);
    }


    /**
     * php index.php zrun_cmd,crontab,5,595 adm
     * @param $mod
     */
    public function crontab($mod)
    {
        print_r($mod);

        $time_sleep     = (int)$mod[1];
        $time_live      = (int)$mod[2];
        $time_sleep     = $time_sleep <= 1  ? 1  : $time_sleep;
        $time_live      = $time_live  <= 60 ? 60 : $time_live;

        $time_curr      = time();
        $time_past      = 0;
        $times          = 0;

        $task_manage    = new manage($this->_db_zrun);
        $task_manage->init();
        do{
            $run_time   = 0-microtime(true);
            $task_manage->run_all();
            $run_time += microtime(true);
            echo "-------exec:".str_pad(round($run_time,4).'s', 8)."  ".
                       "sleep:".str_pad($time_sleep, 5)." \$times:".str_pad($times, 5)."  ".
                    "PastTime:".str_pad($time_past, 5)." \$live:".str_pad($time_live, 5)."\n";
            sleep($time_sleep);
            $times++;
            $time_past   = time() - $time_curr;
        }while($time_past <= $time_live);
    }
}
