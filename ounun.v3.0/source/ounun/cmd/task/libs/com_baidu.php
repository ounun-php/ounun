<?php

namespace ounun\cmd\task\libs;

class com_baidu
{
    /** Mip Baidu API */
    const api_baidu_mip = 'http://data.zz.baidu.com/urls?site={$site}&token={$token}&type=mip';
    /** PC  Baidu API */
    const api_baidu_pc = 'http://data.zz.baidu.com/urls?site={$site}&token={$token}';
    /** Wap Baidu API */
    const api_baidu_wap = 'http://data.zz.baidu.com/urls?site={$site}&token={$token}';

    /** 历史内容接口 - 熊掌号 API  */
    const api_xzh_batch = 'http://data.zz.baidu.com/urls?appid={$appid}&token={$token}&type=batch';
    /** 新增内容接口 - 熊掌号 API  */
    const api_xzh_realtime = 'http://data.zz.baidu.com/urls?appid={$appid}&token={$token}&type=realtime';


    const type_baidu_mip = 1;
    const type_baidu_pc = 2;
    const type_baidu_wap = 5;
    const type_baidu_xzh_realtime = 3;
    const type_baidu_xzh_batch = 4;

    const type = [
        self::type_baidu_mip => '百度[MIP]',
        self::type_baidu_pc => '百度[PC]',
        self::type_baidu_wap => '百度[WAP]',
        self::type_baidu_xzh_realtime => '熊掌号[实时]',
        self::type_baidu_xzh_batch => '熊掌号[历史]',
    ];

    /** 全部提交频率 45天 */
    const push_rate = 3888000;  // 3600 * 24 * 45
    /** 站点 每次  每次提交数量 */
    const max_push_step = 1000;
    /** 熊掌号 - 当天 - 每次提交数量  */
    const max_push_xzh_doday = 10;
    /** 网址地图 单页最大数量 */
    const max_sitemaps_page = 5000;

    /** 接口最大提交量 每天 */
    const push_max = [
        self::type_baidu_mip => 10000,
        self::type_baidu_pc => 5000000,
        self::type_baidu_wap => 5000000,
        self::type_baidu_xzh_realtime => 10,
        self::type_baidu_xzh_batch => 5000000,
    ];

    /** "always", "hourly", "daily", "weekly", "monthly", "yearly" */
    const changefreq_always = "always";
    const changefreq_hourly = "hourly";
    const changefreq_daily = "daily";
    const changefreq_weekly = "weekly";
    const changefreq_monthly = "monthly";
    const changefreq_yearly = "yearly";
}
