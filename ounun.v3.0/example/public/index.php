<?php
/** 设时区 */
// date_default_timezone_set('Asia/Chongqing');
/** P3P **/
// header('P3P: CP="COR NOI CURa ADMa DSP DEVa PSAa PSDa OUR IND UNI PUR NAV"');
/** 根目录 **/
define('Dir_Root', realpath(__DIR__ . '/../') . '/');
/** template目录 **/
define('Dir_Template', Dir_Root . 'template/');
/** libs库文件目录 **/
define('Dir_Ounun', Dir_Root . 'vendor/ounun/');
/** 加载start文件 */
require Dir_Ounun . 'start.v3.php';
/** 开始 */
\ounun\start_web(); // control panel
