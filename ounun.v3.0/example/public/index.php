<?php
/** 设时区 */
// date_default_timezone_set('Asia/Chongqing');
/** P3P **/
// header('P3P: CP="COR NOI CURa ADMa DSP DEVa PSAa PSDa OUR IND UNI PUR NAV"');
/** 根目录 **/
define('Dir_Root' ,   __DIR__.'/../'     );
/** libs库文件目录 **/
define('Dir_Ounun',  '/data/ounun.v3.0/' );
/** 加载Ounun.php文件 */
require Dir_Ounun . 'start.v3.php';
/** 开始 */
start($argv);
