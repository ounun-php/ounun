<?php

/**
 * Created by PhpStorm.
 * User: dreamxyp
 * Date: 2019/3/22
 * Time: 11:39
 */
class c extends \ounun\mvc\c
{
    /** @var int 失败 - 日志状态 */
    const Logs_No  = 0;

    /** @var int 成功 - 日志状态 */
    const Logs_Yes = 1;

    /** 后台登录,日志状态 */
    const Logs = [0 => "失败", 1 => "成功"];

    /** 站点tag */
    const Cache_Tag_Site = 'biz';

    /** 翻页配制 */
    const Page_Config_B = [
        'default' => ['', ''],
        'now' => ['<b>', '</b>', ' '],
        'tag' => ['第一页', '上一页', '下一页', '最后一页'],
        'index' => ['/list_{total_page}.html', '/']
    ];

    /** 翻页配制 */
    const Page_Config_Li = [
        'default' => ['<li>', '</li>'],
        'now' => ['<li class="active">', '</li>', ' '],
        'tag' => ['第一页', '上一页', '下一页', '最后一页'],
        'index' => ['/list_{total_page}.html', '/']
    ];

    /** @var array 服务器类型 */
    const Host_Type = [
        'web' => 'Web服务器',
        'db' => 'MySQL服务器'
    ];

    /** @var string Web服务器 */
    const Host_Type_Web = 'web';

    /** @var string MySQL服务器 */
    const Host_Type_Db = 'db';
}