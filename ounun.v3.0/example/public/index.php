<?php
/** 设时区 */
// date_default_timezone_set('Asia/Chongqing');
/** P3P **/
// header('P3P: CP="COR NOI CURa ADMa DSP DEVa PSAa PSDa OUR IND UNI PUR NAV"');
/** 根目录 **/
define('Dir_Root',   			realpath(__DIR__) .'/');
/** libs库文件目录 **/
define('Dir_Lib',               '/data/ounun.v3.0/');
/** libs目录 **/
define('Dir_Libs_ProJ',         Dir_Root.'proj.libs/');
/** data目录 **/
define('Dir_Data_ProJ',         Dir_Root.'proj.data/');
/** cache目录 **/
define('Dir_Cache',          	Dir_Root.'proj.cache/');
/** URL根  */
define('Lang_Default',	        'en');
/** 加载Ounun.php文件 */
require Dir_Lib    			  . 'ounun.v2.1.php';
/** 加载scfg.global.ini.php文件 */
require Dir_Libs_ProJ         . 'scfg.global.ini.php';
// 解析URL
if($argv && $argv[1])
{
    // error_reporting(E_ALL ^ E_NOTICE);
    $mod  = $argv[1];
    $mod  = explode(',', $mod);
    $host = 'adm.happyuc.org';
    if('zrun_' != substr($mod[0],0,5) )
    {
        exit("error php shell only:zrun_*\n");
    }
    // /usr/bin/php /Users/dreamxyp/Transcend/happyuc-project/go.happyuc.org/index.php zrun_back,run_1m    //   每分钟调一次    **:*1 或 **:*0
    // /usr/bin/php /Users/dreamxyp/Transcend/happyuc-project/go.happyuc.org/index.php zrun_back,run_5m    // 每五分钟调一次    **:*5 或 **:*0
    // /usr/bin/php /Users/dreamxyp/Transcend/happyuc-project/go.happyuc.org/index.php zrun_back,run_10m   // 每十分钟调一次    **:*5 或 **:*0
    // /usr/bin/php /Users/dreamxyp/Transcend/happyuc-project/go.happyuc.org/index.php zrun_back,run_1h    // 每一个小时调一次  **:59
    // /usr/bin/php /Users/dreamxyp/Transcend/happyuc-project/go.happyuc.org/index.php zrun_back,run_4h    // 每四小时调一次    */4:58
    // /usr/bin/php /Users/dreamxyp/Transcend/happyuc-project/go.happyuc.org/index.php zrun_back,run_12h   // 每12小时调一次    03:57  15:57
    // /usr/bin/php /Users/dreamxyp/Transcend/happyuc-project/go.happyuc.org/index.php zrun_back,run_1d    // 每天23：59：00点调一次
    // /usr/bin/php /Users/dreamxyp/Transcend/happyuc-project/go.happyuc.org/index.php zrun_back,run_1w36  // 每期三，六调用一次

    // /usr/bin/php /Users/dreamxyp/Transcend/happyuc-project/go.happyuc.org/index.php zrun_cmd,getblock

    // */5 *    * * * (/home/php/bin/php /data/www.2014/com.yixuew/www.www/index.php zrun_back,Run5M  >> /data/xbsglogs/zrun_ZRunBack_Run5M.log 2>&1)
    // 59  *    * * * (/home/php/bin/php /data/www.2014/com.yixuew/www.www/index.php zrun_back,Run1H  >> /data/xbsglogs/zrun_ZRunBack_Run1H.log 2>&1)
    // 58  */4  * * * (/home/php/bin/php /data/www.2014/com.yixuew/www.www/index.php zrun_back,Run4H  >> /data/xbsglogs/zrun_ZRunBack_Run4H.log 2>&1)
    // 57  */12 * * * (/home/php/bin/php /data/www.2014/com.yixuew/www.www/index.php zrun_back,Run12H >> /data/xbsglogs/zrun_ZRunBack_Run12H.log 2>&1)
    // 59  23   * * * (/home/php/bin/php /data/www.2014/com.yixuew/www.www/index.php zrun_back,Run1D  >> /data/xbsglogs/zrun_ZRunBack_Run1D.log 2>&1)
}else
{
    $uri 	= \ounun::url_original($_SERVER['REQUEST_URI']);
    $mod	= \ounun::url_to_mod($uri);
    $host   = $_SERVER["HTTP_HOST"];
}
/** 初始化scfg */
$dirs = [
    'root'     => Dir_Root,
    'root_app' => '',
];
$libs = [
    'ounun'    => Dir_Lib,
    'cms'      => Dir_Libs_ProJ,
    'app'      => '',
];
$scfg = new scfg($mod,$host,'en',Lang_Default,$dirs,$libs);
/** 开始 */
new ounun($scfg);


