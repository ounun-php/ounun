<?php
session_start();

// adm
$GLOBALS['_scfg']['db']['account_debug'] = [
    'host'       => '127.0.0.1:3306',
    'database'   => 'v2com_moko8_adm',
    'username'   => 'root',
    'password'   => 'root',
    'charset'    => 'utf8',
];
$GLOBALS['_scfg']['db']['account'] = [
    'host'       => 'shihundb001pub.mysql.rds.aliyuncs.com:3306',
    'database'   => 'v2com_moko8_adm',
    'username'   => 'v2cms',
    'password'   => 'kChs2r4s2r716Zd6',
    'charset'    => 'utf8',
];

// libs_v2
$GLOBALS['_scfg']['db']['adm_debug'] = [
    'host'       => '127.0.0.1:3306',
    'database'   => 'v2com_libs_v2',
    'username'   => 'root',
    'password'   => 'root',
    'charset'    => 'utf8'
];
$GLOBALS['_scfg']['db']['adm'] = [
    'host'       => 'shihundb001pub.mysql.rds.aliyuncs.com:3306',
    'database'   => 'v2com_libs_v2',
    'username'   => 'v2cms',
    'password'   => 'kChs2r4s2r716Zd6',
    'charset'    => 'utf8'
];

// libs_v1
$GLOBALS['_scfg']['db']['libs_v1_debug'] = [
    'host'       => '127.0.0.1:3306',
    'database'   => 'v2com_libs_v1',
    'username'   => 'root',
    'password'   => 'root',
    'charset'    => 'utf8'
];
$GLOBALS['_scfg']['db']['libs_v1'] = [
    'host'       => 'shihundb001pub.mysql.rds.aliyuncs.com:3306',
    'database'   => 'v2com_libs_v1',
    'username'   => 'v2cms',
    'password'   => 'kChs2r4s2r716Zd6',
    'charset'    => 'utf8'
];

$GLOBALS['_scfg']['libs'] = [
    'libs_v1'    => ['db'=>'libs_v1','name'=>'库v1',    'table'=>[
        'pic' => [
            'pics_6mm'  => '源6mm.cc',
            'pics_99mm' => '源(九妹)99mm.me',
            'pics_jpg'  => '源(妹子图)mmjpg.com',
            'pics_mm131'=> '源mm131.com',
            'pics_xgyw' => '源xgyw.cc',
        ]
     ]],
    'libs_v2'    => ['db'=>'adm',    'name'=>'库v2(主)','table'=>[

     ]],
];

class adm_purv extends \admin\purview
{
    const cp_site      = 'cp_site';

    const cp_zqun      = 'cp_zqun';

    const cp_libs      = 'cp_libs';

    /** 导航头 要显示 站点 */
    const nav_site     = 10;
    /** 导航头 要显示 站点 */
    const nav_libs     = 20;

    /** 网站后台配 */
    public $cfg  = [
        '{$powered_corp_name}'         => "moko8.com",
        '{$powered_corp_name_mini}'    => "moko8",
        '{$powered_corp_url}'          => "http://adm.moko8.com/",

        '{$powered_studio_name}'       => "moko8.com",
        '{$powered_studio_url}'        => "https://www.moko8.com/",
    ];

    /** 游戏名 与 LOGO */
    public $cfg_name = [
         'adm.moko8.com'      => ['dir'=>"logo/moko8/", 'name'=> "Moko8(release)"],
        'adm2.moko8.com'      => ['dir'=>"logo/moko8/", 'name'=> "Moko8(local)"],
        'adm3.moko8.com'      => ['dir'=>"logo/moko8/", 'name'=> "Moko8(dev)"],
    ];

    /** table */
    public $db_adm            = '`user`';
    public $db_logs_login     = '`logs_login`';
    public $db_logs_act       = '`logs_act`';

    /** IP限定 */
    public $max_ips           = 20;
    public $max_ip            = 5;

    public $purview_default   = 'info';
    public $purview_tree_coop = [10,20];
    public $purview_tree_root = [10,20,50];

    /** 后台根目录 */
    public $purview_line      = 40;

    /** 邮件仙玉审核权限 */
    public $purview_check     = 40;

    /** 权限分类 */
    public $purview_group     = [
        10 => '站长(老板)',
        20 => '核心成员',
        30 => '系统管理员(SA)',

        32 => '运营B(高级)',
        50 => '分渠(客服总监)',
        55 => '分渠(公会推广)',
        60 => '分渠(客服)'
    ];

    const p_all     = [10,20,30,32,50,55,60];

    public $purview = [
        'info'	    => self::_info ,

        'task'      => self::_task ,
        'site'      => self::_site ,

        'coll'      => self::_coll    ,
        'site_content'   => self::_site_content ,
        'site_update'    => self::_site_update  ,

     // 'discover'  => self::_discover ,
     // 'report'    => self::_report ,
     // 'content'   => self::_content ,
        'sys'       => self::_sys ,
    ];

    /**
     * 系统管理 -- 权限控制
     * @var array
     */
    const _info = [
        'name' 		=> '系统',
        'default'	=> 'sys_adm/welcome.html',
        'sub'  		=> [
            'index' => [
                'name'	=> '欢迎',
                'url'	=> 'sys_adm/welcome.html',
                'key'	=>  self::p_all,
            ],
//          'config' => [
//              'name'	=> '配制',
//              'url'	=> 'info/config.html',
//              'key'	=>  self::p_all,
//          ],
        ],
    ];

    /* ****************************************************************
     *  内容
     * **************************************************************** */
    const _site_content = [
        'name' 		=> '内容',
        'default'	=> 'content/pics_list.html',
        'sub'  => [
            'pics_list'     => [
                'name'	=> '图片',
                'url'	=> 'content/pics_list.html',
                'key'	=>  self::p_all,
            ],
        ],
    ];

    /* ****************************************************************
     *  采集
     * **************************************************************** */
    const _coll = [
        'name' 		=> '采集',
        'default'	=> 'coll/pics_list.html',
        'sub'  => [
            'pics_list'     => [
                'name'	=> '图片(美女)',
                'url'	=> 'coll/pics_list.html',
                'key'	=>  self::p_all,
            ],
            'star_av_list'     => [
                'name'	=> '明星(AV)',
                'url'	=> 'coll/star_av_list.html',
                'key'	=>  self::p_all,
            ],
            'star_nv_list'     => [
                'name'	=> '明星(美女)',
                'url'	=> 'coll/star_nv_list.html',
                'key'	=>  self::p_all,
            ],
            'star_mote_list'     => [
                'name'	=> '明星(美女模特)',
                'url'	=> 'coll/star_mote_list.html',
                'key'	=>  self::p_all,
            ],
            'star_movie_list'     => [
                'name'	=> '明星(电影)',
                'url'	=> 'coll/star_movie_list.html',
                'key'	=>  self::p_all,
            ],
        ],
    ];

    /* ****************************************************************
     *  更新
     * **************************************************************** */
    const _site_update = [
        'name' 		=> '更新',
        'default'	=> 'update/pics_list.html',
        'sub'  => [
            'pics_list'     => [
                'name'	=> '图片',
                'url'	=> 'update/pics_list.html',
                'key'	=>  self::p_all,
            ],
//            'invite' => [
//                'name'	=> '邀请报表',
//                'url'	=> 'update/invite.html',
//                'key'	=>  self::p_all,
//            ],
        ],
    ];

    /* ****************************************************************
     *  发现
     * **************************************************************** */
//    const _discover = [
//        'name' 		=> '发现',
//        'default'	=> 'info/config.html?mod_id=2',
//        'sub'  => [
//
//            [
//                'name'	=> '新闻',
//                'data'	=> [
//                    'news' => [
//                        'name'	=> '新闻',
//                        'url'	=> 'discover/news.html',
//                        'key'	=>  self::p_all,
//                    ],
//                    'news_add' => [
//                        'name'	=> '新闻 - 发布',
//                        'url'	=> 'discover/news_add.html',
//                        'key'	=>  self::p_all,
//                    ],
//                ],
//            ],
//            [
//                'name'	=> 'Dapp应用',
//                'data'	=> [
//                    'dapp' => [
//                        'name'	=> 'Dapp应用',
//                        'url'	=> 'info/config.html?mod_id=3',
//                        'key'	=>  self::p_all,
//                    ],
//                    'config' => [
//                        'name'	=> '头部大图广告',
//                        'url'	=> 'info/config.html?mod_id=2',
//                        'key'	=>  self::p_all,
//                    ],
//                ],
//            ],
//        ],
//    ];

    /* ****************************************************************
     *  统计报表
     * **************************************************************** */
//    const _report = [
//        'name' 		=> '报表',
//        'default'	=> 'report/',
//        'sub'  => [
//            'report' => [
//                'name'	=> '结算报表',
//                'url'	=> 'report/lists.html',
//                'key'	=>  self::p_all,
//            ],
//            'invite' => [
//                'name'	=> '邀请报表',
//                'url'	=> 'report/invite.html',
//                'key'	=>  self::p_all,
//            ],
//        ],
//    ];

    /* ****************************************************************
     *  定时任务
     * **************************************************************** */
    const _task = [
        'name' 		=> '任务',
        'default'	=> 'task/logs.html',
        'sub'  => [
            'logs'   => [
                'name'	=> '任务日志',
                'url'	=> 'task/logs.html',
                'key'	=>  self::p_all,
            ],
            'index' => [
                'name'	=> '定时任务',
                'url'	=> 'task/',
                'key'	=>  self::p_all,
            ],
        ],
    ];

    /* ****************************************************************
     *  站点
     * **************************************************************** */
    const _site = [
        'name' 		=> '站点',
        'default'	=> 'site/site_list.html',
        'sub'  => [
            'site_list'     => [
                'name'	=> '站点',
                'url'	=> 'site/site_list.html',
                'key'	=>  self::p_all,
            ],
            'host_list'     => [
                'name'	=> '服务器',
                'url'	=> 'site/host_list.html',
                'key'	=>  self::p_all,
            ],
            'zqun_list'     => [
                'name'	=> '站群列表',
                'url'	=> 'site/zqun_list.html',
                'key'	=>  self::p_all,
            ],
            [
                'name'	=> '站点',
                'data'	=> [
                    'config_list'   => [
                        'name'	=> '站点配制',
                        'url'	=> 'site/config_list.html',
                        'key'	=>  self::p_all,
                    ],
                    'link' => [
                        'name'	=> '站点友情连接',
                        'url'	=> 'site/link.html',
                        'key'	=>  self::p_all,
                    ],
                    'sitemap_list' => [
                        'name'	=> '站点地图',
                        'url'	=> 'site/sitemap_list.html',
                        'key'	=>  [10,20,30],
                    ],
                    'sitemap_stat_map' => [
                        'name'	=> '站点地图[统计]',
                        'url'	=> 'site/sitemap_stat_map.html',
                        'key'	=>  [10,20,30],
                    ],
                    'sitemap_stat' => [
                        'name'	=> '站点push统计',
                        'url'	=> 'site/sitemap_stat.html',
                        'key'	=>  [10,20,30],
                    ],
                ],
            ],
        ],
    ];


    /* ****************************************************************
     *  管理
    * **************************************************************** */
    const _sys = [
        'name' 	  => '管理',
        'default' => 'sys_adm/password.html',
        'sub'  => [
            'password' => [
                'name'	=> '密码更新',
                'url'	=> 'sys_adm/password.html',
                'key'	=>  self::p_all,
            ],
            'google' => [
                'name'	=> '谷歌动态验证',
                'url'	=> 'sys_adm/google.html',
                'key'	=>  self::p_all,
            ],
            [
                'name'	=> '管理员管理',
                'data'	=> [
                    'adm_add' => [
                        'name'	=> '添加管理人员',
                        'url'	=> 'sys_adm/adm_add.html',
                        'key'	=>  [10,20,32],
                    ],
                    'adm_list' => [
                        'name'	=> '管理员列表',
                        'url'	=> 'sys_adm/adm_list.html',
                        'key'	=>  [10,20,32],
                    ],
                ],
            ],
            [
                'name'	=> '日志',
                'data'	=> [
                    'logs_login' => [
                        'name'	=> '登录日志',
                        'url'	=> 'sys_adm/logs_login.html',
                        'key'	=>  [10,20,30],
                    ],
                    'logs_act' => [
                        'name'	=> '操作日志',
                        'url'	=> 'sys_adm/logs_act.html',
                        'key'	=>  [10,20,30],
                    ],
                ],
            ],
        ],
    ];
}
