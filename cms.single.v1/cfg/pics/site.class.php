<?php
namespace cfg\pics;


class site extends \cfg\site
{
    /** @var array 模块 */
    const mods    = [
        2  => '美女明星',
        3  => '娱乐新闻',
        4  => '美女图库',
        11 => '专题&标识',
    ];

    const mod_star   = 2;
    const mod_news   = 3;
    const mod_pics   = 4;
    const mod_tags   = 11;
    // 首页
    const mod_index  = 21; // 1144  2418  298  330  300


    const maps = [
        'tag'      => 13,  13  => 'tag',
        'special'  => 11,  11  => 'special',
        'star'     => 2,    2  => 'star',

        'youhuo'   => 4,   4   => 'youhuo',
        'mote'     => 5,   5   => 'mote',
        'meiru'    => 6,   6   => 'meiru',
        'qingchun' => 7,   7   => 'qingchun',
        'meitui'   => 8,   8   => 'meitui',
        'cosplay'  => 9,   9   => 'cosplay',

        'gossip'   => 3,   3   => 'gossip',
        'yule'     => 12,  12  => 'yule',
    ];

    /** 图片 */
    const pics = [
        'youhuo'   => '深V诱惑',
        'mote'     => '性感模特',
        'meiru'    => '乳色乳香',
        'qingchun' => '清新养眼',
        'meitui'   => '美腿皇后',
        'cosplay'  => '动漫美眉',

        ''         => '美女图片',
        'top'      => '精选美图榜',
        'news'     => '最新更新',
        //'hot'      => '最火图片',
        'rec'      => '编辑推荐',
    ];

    const pics_youhuo    = 4;
    const pics_mote      = 5;
    const pics_meiru     = 6;
    const pics_qingchun  = 7;
    const pics_meitui    = 8;
    const pics_cosplay   = 9;

    const pics_nav       = ['youhuo','mote','meiru','qingchun','meitui','cosplay'];

    /** 娱乐新闻 */
    const news = [
        'gossip'    => '娱乐新闻',
        'yule'      => '图文八卦',

        ''          => '娱乐八卦',
    ];

    const news_gossip  =  3;
    const news_yule    = 12;

    /** 娱乐专题 */
    const zhuanti = [
        'tag'       => '娱乐标识',
        'special'   => '美女专题',
        'star'      => '美女明星',
    ];

    const zhuanti_star     =  2;
    const zhuanti_special  = 11;
    const zhuanti_tag      = 13;
}