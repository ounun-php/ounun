<?php
namespace cfg;

class site
{
    /** @var array 分页配制  */
    const cfg_page = [
        'default' => ['' , ''] ,
        'now'     => ['' , '', ' class="thisclass" ']  ,
        'tag'     => ['第一页' , '上一页' , '下一页' , '最后一页'],
        'index'   => ['/list_{total_page}.html' , '/']
    ];
}