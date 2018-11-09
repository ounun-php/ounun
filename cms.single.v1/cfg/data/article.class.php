<?php

namespace cfg\data;


class article extends \cfg\site
{
    /** @var array 分页配制  */
    const cfg_page = [
        'default' => ['' , ''] ,
        'now'     => ['' , '', ' class="current" ']  ,
        'tag'     => ['第一页' , '上一页' , '下一页' , '最后一页'],
        'index'   => ['/list_{total_page}.html' , '/']
    ];
    /** @var array 模块 */
    const mods    = [
        102  => '猎奇图库',
        103  => '猎奇新闻',
        104  => '专题',
        105  => '标识',
    ];

    /** @var int 猎奇图库 */
    const mod_pics      = 102;
    /** @var int 猎奇新闻 */
    const mod_news      = 103;
    /** @var int 专题 */
    const mod_special   = 104;
    /** @var int 标识 */
    const mod_tags      = 105;

    /** @var int 历史 */
    const mulu_lishi    = 10301;
    /** @var int 军事 */
    const mulu_junshi   = 10306;
    /** @var int 探索 */
    const mulu_tansuo   = 10311;
    /** @var int 自然 */
    const mulu_ziran    = 10316;
    /** @var int 社会 */
    const mulu_shehui   = 10321;

    /** @var array 目录 */
    const mulu = [
        'lishi'      		=> [ 'name' => '历史'	, 'title' => '历史真相',	'parent' => 'lishi'  ,'category_id' => 10301, 'category_sub' => 1030101, 'type' => 'article',	'range' =>  ['75'] ],
            'kaogu'  		=> [ 'name' => '考古发现'	, 'title' => '考古发现',	'parent' => 'lishi'  ,'category_id' => 10301, 'category_sub' => 1030106, 'type' => 'article',	'range' =>  ['75'] ],
            'yishi'			=> [ 'name' => '名人轶事'	, 'title' => '名人轶事',	'parent' => 'lishi'  ,'category_id' => 10301, 'category_sub' => 1030111, 'type' => 'article',	'range' =>  ['75'] ],
            'zhengshi'    	=> [ 'name' => '正史轶闻'	, 'title' => '正史轶闻',	'parent' => 'lishi'  ,'category_id' => 10301, 'category_sub' => 1030116, 'type' => 'article',	'range' =>  ['75'] ],
            'yeshi'   		=> [ 'name' => '野史趣闻'	, 'title' => '野史趣闻',	'parent' => 'lishi'  ,'category_id' => 10301, 'category_sub' => 1030121, 'type' => 'article',	'range' =>  ['75'] ],
            'yiyu'   		=> [ 'name' => '异域春秋'	, 'title' => '异域春秋',	'parent' => 'lishi'  ,'category_id' => 10301, 'category_sub' => 1030126, 'type' => 'article',	'range' =>  ['75'] ],
            'laozhaopian' 	=> [ 'name' => '老照片'	, 'title' => '老照片',	'parent' => 'lishi'  ,'category_id' => 10301, 'category_sub' => 1030131, 'type' => 'article',	'range' =>  ['75'] ],

        'junshi'   			=> [ 'name' => '军事'	, 'title' => '军事揭秘',	'parent' => 'junshi' ,'category_id' => 10306, 'category_sub' => 1030601, 'type' => 'article',	'range' =>  ['75'] ],
            'junqingmima'   => [ 'name' => '军情密码'	, 'title' => '军情密码',	'parent' => 'junshi' ,'category_id' => 10306, 'category_sub' => 1030606, 'type' => 'article',	'range' =>  ['75'] ],
            'junshirenwu'   => [ 'name' => '军事人物'	, 'title' => '军事人物',	'parent' => 'junshi' ,'category_id' => 10306, 'category_sub' => 1030611, 'type' => 'article',	'range' =>  ['75'] ],
            'zhanzhengmiti' => [ 'name' => '战争谜题'	, 'title' => '战争谜题',	'parent' => 'junshi' ,'category_id' => 10306, 'category_sub' => 1030616, 'type' => 'article',	'range' =>  ['75'] ],
            'wuqitanmi'     => [ 'name' => '武器探秘'	, 'title' => '武器探秘',	'parent' => 'junshi' ,'category_id' => 10306, 'category_sub' => 1030621, 'type' => 'article',	'range' =>  ['75'] ],

        'tansuo'   			=> [ 'name' => '探索'	, 'title' => '科技探索',	'parent' => 'tansuo' ,'category_id' => 10311, 'category_sub' => 1031101, 'type' => 'article',	'range' =>  ['75'] ],
            'yuzhouaomi'   	=> [ 'name' => '宇宙奥秘'	, 'title' => '宇宙奥秘',	'parent' => 'tansuo' ,'category_id' => 10311, 'category_sub' => 1031106, 'type' => 'article',	'range' =>  ['75'] ],
            'ufo'   		=> [ 'name' => 'UFO之谜'	, 'title' => 'UFO之谜',	'parent' => 'tansuo' ,'category_id' => 10311, 'category_sub' => 1031111, 'type' => 'article',	'range' =>  ['75'] ],
            'faming'   		=> [ 'name' => '前沿发明'	, 'title' => '前沿发明',	'parent' => 'tansuo' ,'category_id' => 10311, 'category_sub' => 1031116, 'type' => 'article',	'range' =>  ['75'] ],
            'chuangyi'   	=> [ 'name' => '创意概念'	, 'title' => '创意概念',	'parent' => 'tansuo' ,'category_id' => 10311, 'category_sub' => 1031121, 'type' => 'article',	'range' =>  ['75'] ],
            'shuma'   		=> [ 'name' => '数码科技'	, 'title' => '数码科技',	'parent' => 'tansuo' ,'category_id' => 10311, 'category_sub' => 1031126, 'type' => 'article',	'range' =>  ['75'] ],
            'hlw'   		=> [ 'name' => '互联网'	, 'title' => '互联网',	'parent' => 'tansuo' ,'category_id' => 10311, 'category_sub' => 1031131, 'type' => 'article',	'range' =>  ['75'] ],

        'ziran'   			=> [ 'name' => '自然'	, 'title' => '自然密码',	'parent' => 'ziran'  ,'category_id' => 10316, 'category_sub' => 1031601, 'type' => 'article',	'range' =>  ['75'] ],
            'qqdw'   		=> [ 'name' => '奇趣动物'	, 'title' => '奇趣动物',	'parent' => 'ziran'  ,'category_id' => 10316, 'category_sub' => 1031611, 'type' => 'article',	'range' =>  ['75'] ],
            'zrxx'   		=> [ 'name' => '自然现象'	, 'title' => '自然现象',	'parent' => 'ziran'  ,'category_id' => 10316, 'category_sub' => 1031616, 'type' => 'article',	'range' =>  ['75'] ],
            'zhiwu'   		=> [ 'name' => '怪异植物'	, 'title' => '怪异植物',	'parent' => 'ziran'  ,'category_id' => 10316, 'category_sub' => 1031621, 'type' => 'article',	'range' =>  ['75'] ],
            'dili'   		=> [ 'name' => '地理风光'	, 'title' => '地理风光',	'parent' => 'ziran'  ,'category_id' => 10316, 'category_sub' => 1031631, 'type' => 'article',	'range' =>  ['75'] ],

        'shehui'   			=> [ 'name' => '社会'	, 'title' => '社会百态',	'parent' => 'shehui' ,'category_id' => 10321, 'category_sub' => 1032101, 'type' => 'article',	'range' =>  ['75'] ],
            'tuku'   		=> [ 'name' => '图说天下'	, 'title' => '图说天下',	'parent' => 'shehui' ,'category_id' => 10321, 'category_sub' => 1032106, 'type' => 'article',	'range' =>  ['75'] ],
            'quwen'   		=> [ 'name' => '奇闻趣事'	, 'title' => '奇闻趣事',	'parent' => 'shehui' ,'category_id' => 10321, 'category_sub' => 1032111, 'type' => 'article',	'range' =>  ['75'] ],
            'lingyi'   		=> [ 'name' => '灵异奇谈'	, 'title' => '灵异奇谈',	'parent' => 'shehui' ,'category_id' => 10321, 'category_sub' => 1032116, 'type' => 'article',	'range' =>  ['75'] ],
            'qipa'   		=> [ 'name' => '极品奇葩'	, 'title' => '极品奇葩',	'parent' => 'shehui' ,'category_id' => 10321, 'category_sub' => 1032121, 'type' => 'article',	'range' =>  ['75'] ],
            'bagua'   		=> [ 'name' => '绯闻八卦'	, 'title' => '绯闻八卦',	'parent' => 'shehui' ,'category_id' => 10321, 'category_sub' => 1032126, 'type' => 'article',	'range' =>  ['75'] ],
            'fengsu'   		=> [ 'name' => '奇风异俗'	, 'title' => '奇风异俗',	'parent' => 'shehui' ,'category_id' => 10321, 'category_sub' => 1032131, 'type' => 'article',   'range' =>  ['75'] ],
    ];

    protected static $_mulu_maps = [];

    public static function mulu_maps()
    {
        if(self::$_mulu_maps)
        {
            return self::$_mulu_maps;
        }
        foreach (self::mulu as $k=>$v){
            $v['key']             = $k;
            $v['parent_name']     = self::mulu[$v['parent']]['name'];
            self::$_mulu_maps[$k] = $v;
            self::$_mulu_maps[$v['category_id']] = $v;
            self::$_mulu_maps[$v['category_sub']] = $v;
        }
        return self::$_mulu_maps;
    }
}