<?php
namespace controller;

class zrun_back extends \v
{
    /**
     * 每五分钟调一次
     * **:*5 或 **:*0
     */
    public function run_5m($mod)
    {

//        $b = 'a:48:{i:0;s:18:"99mm/2742/1-jk.jpg";i:1;s:18:"99mm/2742/2-mg.jpg";i:2;s:18:"99mm/2742/3-v6.jpg";i:3;s:18:"99mm/2742/4-x3.jpg";i:4;s:18:"99mm/2742/5-sv.jpg";i:5;s:18:"99mm/2742/6-pc.jpg";i:6;s:18:"99mm/2742/7-51.jpg";i:7;s:18:"99mm/2742/8-x3.jpg";i:8;s:18:"99mm/2742/9-k6.jpg";i:9;s:19:"99mm/2742/10-qh.jpg";i:10;s:19:"99mm/2742/11-hk.jpg";i:11;s:19:"99mm/2742/12-l3.jpg";i:12;s:19:"99mm/2742/13-vh.jpg";i:13;s:19:"99mm/2742/14-92.jpg";i:14;s:19:"99mm/2742/15-qf.jpg";i:15;s:19:"99mm/2742/16-94.jpg";i:16;s:19:"99mm/2742/17-ri.jpg";i:17;s:19:"99mm/2742/18-i3.jpg";i:18;s:19:"99mm/2742/19-oy.jpg";i:19;s:19:"99mm/2742/20-b8.jpg";i:20;s:19:"99mm/2742/21-uy.jpg";i:21;s:19:"99mm/2742/22-fl.jpg";i:22;s:19:"99mm/2742/23-y7.jpg";i:23;s:19:"99mm/2742/24-pk.jpg";i:24;s:19:"99mm/2742/25-47.jpg";i:25;s:19:"99mm/2742/26-3g.jpg";i:26;s:19:"99mm/2742/27-nd.jpg";i:27;s:19:"99mm/2742/28-7t.jpg";i:28;s:19:"99mm/2742/29-h9.jpg";i:29;s:19:"99mm/2742/30-ry.jpg";i:30;s:19:"99mm/2742/31-95.jpg";i:31;s:19:"99mm/2742/32-7g.jpg";i:32;s:19:"99mm/2742/33-cv.jpg";i:33;s:19:"99mm/2742/34-ky.jpg";i:34;s:19:"99mm/2742/35-2x.jpg";i:35;s:19:"99mm/2742/36-lx.jpg";i:36;s:19:"99mm/2742/37-s1.jpg";i:37;s:19:"99mm/2742/38-6u.jpg";i:38;s:19:"99mm/2742/39-ci.jpg";i:39;s:19:"99mm/2742/40-yj.jpg";i:40;s:19:"99mm/2742/41-me.jpg";i:41;s:19:"99mm/2742/42-uh.jpg";i:42;s:19:"99mm/2742/43-k4.jpg";i:43;s:19:"99mm/2742/44-lv.jpg";i:44;s:19:"99mm/2742/45-4p.jpg";i:45;s:19:"99mm/2742/46-tc.jpg";i:46;s:19:"99mm/2742/47-sb.jpg";i:47;s:19:"99mm/2742/48-ly.jpg";}';
//        $c =  unserialize($b);

    }
    /**
     * 每一个小时调一次
     * **:59
     */
    public function run_1h($mod)
    {

    }


    /**
     * 每四小时调一次
     * *4:58
     */
    public function run_4h($mod)
    {

    }


    /**
     * 每12小时调一次
     * 03:57  15:57
     */
    public function run_12h($mod)
    {

    }

    /**
     * 每天23：59：00点调一次
     */
    public function run_1d($mod)
    {

    }
}
