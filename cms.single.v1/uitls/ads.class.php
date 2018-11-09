<?php

namespace uitls;

class ads
{
    const m  = [
        // 通栏1
        'moko8-250-160'   => '<script type="text/javascript" src="//bd-c.4uo.cn/ojbflrkgp.js"></script>',
        'moko8-960-90'    => '<script type="text/javascript" src="//bd-c.4uo.cn/rmeiouobj.js"></script>',
    ];

    const mip_20_3        = '<mip-ad type="baidu-wm-ext" domain="bd-c.4uo.cn" token="lgycfymio"><div id="lgycfymio"></div></mip-ad>';

    const mip_20_5        = '<mip-ad type="baidu-wm-ext" domain="bd-c.4uo.cn" token="bwosvtfcs"><div id="bwosvtfcs"></div></mip-ad>';

    const mip_list_big    = '<mip-ad type="baidu-wm-ext" domain="bd-c.4uo.cn" token="dyquxuexn"><div id="dyquxuexn"></div></mip-ad>';

    const mip_2           = self::mip_list_big;

    const mip_go_url      = 'https://api.383434.com/api/mobile_app/';

    const mip_go          = '<a target="_blank" href="'.self::mip_go_url.'"><mip-img src="/static/live/hj4.gif"></mip-img></a>';

    const mip_pay         = self::mip_go;

    const google_h1       = '';


    const m_js            = 'function adwrite(mode,size){ 
                                var show = false; 
                                var str = \'mode:\'+mode+\' size:\'+size; 
                                if(m_gcom && m_gcom[mode]){ 
                                    show = true; 
                                    str = m_gcom[mode]; 
                                } 
                                document.write(str); 
                            }';
}
