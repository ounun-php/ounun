<?php

// ------------------------------ APP ------------------------------
/** 项目站名称 */
define('Const_SiteName', '偶黁(ounun.org)');
/** 项目主域名 */
define('Const_Domain', 'ounun.org');
/** 项目代号    */
define('Const_Code', 'ounun');


/** 静态index  */
define('Const_Static_Idx', 12);
/** 内部服务器与中心服务器通信密码 */
define('Const_Key_Conn_Private', '512009757a6e7f57b78dcd5edf378e67');
/** 百度统计 */
define('Const_Stats_Baidu', 'defac56d41cf2b16cb5d1bfbfd8748ef');
/** cnzz统计 */
define('Const_Stats_Cnzz', '');
/** 备案号 */
define('Const_Site_Benan', '沪ICP备13037221号-14');
/** Baidu API */
define('Const_Baidu_Token', 'rgC3MkBxK9gkQNkL');
/** SiteId_Xzh API */
define('Const_Baidu_Xzh_SiteId', '');
/** Const_Baidu_Xzh_Token API */
define('Const_Baidu_Xzh_Token', '');

/** 设定对应cms类名 */
\ounun\config::set_cms_classname('\\extend\\cms_www', Const_Key_Conn_Private);

/** 设定自动加载目录 */
\ounun\config::add_paths(Dir_Ounun, 'ounun');
\ounun\config::add_paths(Dir_Ounun, 'plugins');
\ounun\config::add_paths(Dir_Root, 'extend');
/** 直接加载 */
\ounun\config::add_class('c', Dir_Extend . 'c.php');

/** 配制cache_file */
\ounun\config::set_global([
    'cache_file' =>
        [
            'type' => \ounun\cache\core::Type_File,
            'mod' => 'html',
            'root' => Dir_Cache,
            'format_string' => false,
            'large_scale' => true,
        ],
]);

/** 配制database */
\ounun\config::set_database([
    'account' =>
        [
            'host' => 'shihundb001pub.mysql.rds.aliyuncs.com:3306',
            'database' => 'v2com_moko8_adm',
            'username' => 'v2cms',
            'password' => 'kChs2r4s2r716Zd6',
            'charset' => 'utf8',
        ],
    'adm' =>
        [
            'host' => 'shihundb001pub.mysql.rds.aliyuncs.com:3306',
            'database' => 'v2com_libs_v2',
            'username' => 'v2cms',
            'password' => 'kChs2r4s2r716Zd6',
            'charset' => 'utf8'
        ],
    'libs_v1' =>
        [
            'host' => 'shihundb001pub.mysql.rds.aliyuncs.com:3306',
            'database' => 'v2com_libs_v1',
            'username' => 'v2cms',
            'password' => 'kChs2r4s2r716Zd6',
            'charset' => 'utf8'
        ],
]);

/** 支持的语言 */
\ounun\config::set_lang_support([
    "en" => "English",
    // "zh"=>"繁體中文",
    "cn" => "简体中文",
    // "ja"=>"日本語",
]);

/** 设定路由数据 */
\ounun\config::set_routes(
    [
        // Const_App
        'm.' . Const_Domain => ['app' => 'www', 'url' => '/', 'tpl_style' => '_wap', 'tpl_default' => '_default'],
        'mip.' . Const_Domain => ['app' => 'www', 'url' => '/', 'tpl_style' => '_mip', 'tpl_default' => '_wap'],
        'www.' . Const_Domain => ['app' => 'www', 'url' => '/', 'tpl_style' => '_default'],
        'api.' . Const_Domain => ['app' => 'api', 'url' => '/', 'tpl_style' => '_default'],
        'adm.' . Const_Domain => ['app' => 'adm', 'url' => '/', 'tpl_style' => '_default'],

        'm' . Environment . '.' . Const_Domain => ['app' => 'www', 'url' => '/', 'tpl_style' => '_wap', 'tpl_default' => '_default'],
        'mip' . Environment . '.' . Const_Domain => ['app' => 'www', 'url' => '/', 'tpl_style' => '_mip', 'tpl_default' => '_wap'],
        'www' . Environment . '.' . Const_Domain => ['app' => 'www', 'url' => '/', 'tpl_style' => '_default'],
        'api' . Environment . '.' . Const_Domain => ['app' => 'api', 'url' => '/', 'tpl_style' => '_default'],
        'adm' . Environment . '.' . Const_Domain => ['app' => 'adm', 'url' => '/', 'tpl_style' => '_default'],
    ],
    ['app' => 'www', 'url' => '/', 'tpl' => '_default']  // default
);

/** 设定路由数据 */
\ounun\config::set_urls(
    'https://www' . Const_Domain . '/',
    'https://m.' . Const_Domain . '/',
    'https://mip.' . Const_Domain . '/',
    '//api.' . Const_Domain . '/',
    '//s.' . Const_Domain . '/',
    '//s.' . Const_Domain . '/s' . Const_Static_Idx . '/',
    '//s.' . Const_Domain . '/u' . Const_Static_Idx . '/',
    '//s.' . Const_Domain . '/g' . Const_Static_Idx . '/',
    Const_Domain
);
