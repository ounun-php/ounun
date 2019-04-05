<?php

namespace app\www\model;

class status
{
    /** 后台登录,日志状态 */
    const logs = [0 => "失败", 1 => "成功"];

    /** 翻页配制 */
    const page_cfg = [
        'default' => ['', ''],
        'now' => ['<b>', '</b>', ' '],
        'tag' => ['第一页', '上一页', '下一页', '最后一页'],
        'index' => ['/list_{total_page}.html', '/']
    ];

    /** 翻页配制 */
    const page_cfg_li = [
        'default' => ['<li>', '</li>'],
        'now' => ['<li class="active">', '</li>', ' '],
        'tag' => ['第一页', '上一页', '下一页', '最后一页'],
        'index' => ['/list_{total_page}.html', '/']
    ];
}
