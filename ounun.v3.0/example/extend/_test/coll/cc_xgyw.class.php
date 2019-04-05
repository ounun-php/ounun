<?php

namespace extend\task\coll;

use ounun\cmd\task\struct;

class cc_xgyw extends \ounun\cmd\task\coll_base\_coll
{

    public function __construct(struct $task_struct, string $tag = '', string $tag_sub = '')
    {
        $this->_tag = 'pic';
        $this->_tag_sub = 'xgyw.cc';

        $this->coll_set('https://www.jpxgyw.com', '`pics_xgyw`', 'yw', '/data/ossfs_io3/', 'libs_v1');

        parent::__construct($task_struct, $tag, $tag_sub);
    }

    /** 列表01 - 《采集》任务 */
    public function list_01()
    {
        $list_url = [
            '/Xgyw/' => 'https://www.jpxgyw.com/Xgyw/page_{page}.html', //  性感尤物,网红女神
            '/Tuigirl/' => 'https://www.jpxgyw.com/Tuigirl/page_{page}.html', // Tuigirl推女郎
            '/Ugirls/' => 'https://www.jpxgyw.com/Ugirls/page_{page}.html', // Ugirls尤果网
            '/Tgod/' => 'https://www.jpxgyw.com/Tgod/page_{page}.html', // Tgod推女神,QingDouKe青豆客
            '/TouTiao/' => 'https://www.jpxgyw.com/TouTiao/page_{page}.html', // TouTiao头条女神
            '/Girlt/' => 'https://www.jpxgyw.com/Girlt/page_{page}.html', // Girlt果团网
            '/Aiyouwu/' => 'https://www.jpxgyw.com/Aiyouwu/page_{page}.html', // Ugirls爱尤物
            '/LEGBABY/' => 'https://www.jpxgyw.com/LEGBABY/page_{page}.html', // LEGBABY美腿宝贝
            '/Rosimeimei/' => 'https://www.jpxgyw.com/Rosimeimei/page_{page}.html', // ROSI写真
            '/MissLeg/' => 'https://www.jpxgyw.com/MissLeg/page_{page}.html', // MissLeg蜜丝
            '/BoLoli/' => 'https://www.jpxgyw.com/BoLoli/page_{page}.html', // 波萝社新刊
            '/Slady/' => 'https://www.jpxgyw.com/Slady/page_{page}.html', // Slady猎女神


            '/Xiuren/' => 'https://www.jpxgyw.com/Xiuren/page_{page}.html', // Xiuren秀人
            '/MyGirl/' => 'https://www.jpxgyw.com/MyGirl/page_{page}.html', // MyGirl美媛馆
            '/YouWu/' => 'https://www.jpxgyw.com/YouWu/page_{page}.html', // YouWu尤物馆
            '/IMiss/' => 'https://www.jpxgyw.com/IMiss/page_{page}.html', // IMiss爱蜜社
            '/MiiTao/' => 'https://www.jpxgyw.com/MiiTao/page_{page}.html', // MiiTao蜜桃社
            '/Uxing/' => 'https://www.jpxgyw.com/Uxing/page_{page}.html', // Uxing优星馆
            '/FeiLin/' => 'https://www.jpxgyw.com/FeiLin/page_{page}.html', // FeiLin嗲囡囡
            '/MiStar/' => 'https://www.jpxgyw.com/MiStar/page_{page}.html', // MiStar魅妍社
            '/Tukmo/' => 'https://www.jpxgyw.com/Tukmo/page_{page}.html', // Tukmo兔几盟
            '/WingS/' => 'https://www.jpxgyw.com/WingS/page_{page}.html', // WingS影私荟
            '/LeYuan/' => 'https://www.jpxgyw.com/LeYuan/page_{page}.html', // LeYuan星乐园
            '/Taste/' => 'https://www.jpxgyw.com/Taste/page_{page}.html', // Taste顽味生活
            '/MFStar/' => 'https://www.jpxgyw.com/MFStar/page_{page}.html', // MFStar模范学院
            '/Huayan/' => 'https://www.jpxgyw.com/Huayan/page_{page}.html', // Huayan花の颜
            '/DKGirl/' => 'https://www.jpxgyw.com/DKGirl/page_{page}.html', // DKGirl御女郎
            '/Candy/' => 'https://www.jpxgyw.com/Candy/page_{page}.html', // Candy糖果画报
            '/YouMi/' => 'https://www.jpxgyw.com/YouMi/page_{page}.html', // YouMi尤蜜荟
            '/MintYe/' => 'https://www.jpxgyw.com/MintYe/page_{page}.html', // MintYe薄荷叶
            '/Micat/' => 'https://www.jpxgyw.com/Micat/page_{page}.html', // Micat猫萌榜
            '/Mtmeng/' => 'https://www.jpxgyw.com/Mtmeng/page_{page}.html', // Mtmeng模特联盟
            '/HuaYang/' => 'https://www.jpxgyw.com/HuaYang/page_{page}.html', // HuaYang花漾Show
            '/XingYan/' => 'https://www.jpxgyw.com/XingYan/page_{page}.html', // XingYan星颜社
        ];
        $list_go = [
            '/Xgyw/' => ['https://www.jpxgyw.com/Xgyw/page_1.html' => 'https://www.jpxgyw.com/Xgyw/'], //  性感尤物,网红女神
            '/Tuigirl/' => ['https://www.jpxgyw.com/Tuigirl/page_1.html' => 'https://www.jpxgyw.com/Tuigirl/'], // Tuigirl推女郎
            '/Ugirls/' => ['https://www.jpxgyw.com/Ugirls/page_1.html' => 'https://www.jpxgyw.com/Ugirls/'], // Ugirls尤果网
            '/Tgod/' => ['https://www.jpxgyw.com/Tgod/page_1.html' => 'https://www.jpxgyw.com/Tgod/'], // Tgod推女神,QingDouKe青豆客
            '/TouTiao/' => ['https://www.jpxgyw.com/TouTiao/page_1.html' => 'https://www.jpxgyw.com/TouTiao/'], // TouTiao头条女神
            '/Girlt/' => ['https://www.jpxgyw.com/Girlt/page_1.html' => 'https://www.jpxgyw.com/Girlt/'], // Girlt果团网
            '/Aiyouwu/' => ['https://www.jpxgyw.com/Aiyouwu/page_1.html' => 'https://www.jpxgyw.com/Aiyouwu/'], // Ugirls爱尤物
            '/LEGBABY/' => ['https://www.jpxgyw.com/LEGBABY/page_1.html' => 'https://www.jpxgyw.com/LEGBABY/'], // LEGBABY美腿宝贝
            '/Rosimeimei/' => ['https://www.jpxgyw.com/Rosimeimei/page_1.html' => 'https://www.jpxgyw.com/Rosimeimei/'], // ROSI写真
            '/MissLeg/' => ['https://www.jpxgyw.com/MissLeg/page_1.html' => 'https://www.jpxgyw.com/MissLeg/'], // MissLeg蜜丝
            '/BoLoli/' => ['https://www.jpxgyw.com/BoLoli/page_1.html' => 'https://www.jpxgyw.com/BoLoli/'], // 波萝社新刊
            '/Slady/' => ['https://www.jpxgyw.com/Slady/page_1.html' => 'https://www.jpxgyw.com/Slady/'], // Slady猎女神


            '/Xiuren/' => ['https://www.jpxgyw.com/Xiuren/page_1.html' => 'https://www.jpxgyw.com/Xiuren/'], // Xiuren秀人
            '/MyGirl/' => ['https://www.jpxgyw.com/MyGirl/page_1.html' => 'https://www.jpxgyw.com/MyGirl/'], // MyGirl美媛馆
            '/YouWu/' => ['https://www.jpxgyw.com/YouWu/page_1.html' => 'https://www.jpxgyw.com/YouWu/'], // YouWu尤物馆
            '/IMiss/' => ['https://www.jpxgyw.com/IMiss/page_1.html' => 'https://www.jpxgyw.com/IMiss/'], // IMiss爱蜜社
            '/MiiTao/' => ['https://www.jpxgyw.com/MiiTao/page_1.html' => 'https://www.jpxgyw.com/MiiTao/'], // MiiTao蜜桃社
            '/Uxing/' => ['https://www.jpxgyw.com/Uxing/page_1.html' => 'https://www.jpxgyw.com/Uxing/'], // Uxing优星馆
            '/FeiLin/' => ['https://www.jpxgyw.com/FeiLin/page_1.html' => 'https://www.jpxgyw.com/FeiLin/'], // FeiLin嗲囡囡
            '/MiStar/' => ['https://www.jpxgyw.com/MiStar/page_1.html' => 'https://www.jpxgyw.com/MiStar/'], // MiStar魅妍社
            '/Tukmo/' => ['https://www.jpxgyw.com/Tukmo/page_1.html' => 'https://www.jpxgyw.com/Tukmo/'], // Tukmo兔几盟
            '/WingS/' => ['https://www.jpxgyw.com/WingS/page_1.html' => 'https://www.jpxgyw.com/WingS/'], // WingS影私荟
            '/LeYuan/' => ['https://www.jpxgyw.com/LeYuan/page_1.html' => 'https://www.jpxgyw.com/LeYuan/'], // LeYuan星乐园
            '/Taste/' => ['https://www.jpxgyw.com/Taste/page_1.html' => 'https://www.jpxgyw.com/Taste/'], // Taste顽味生活
            '/MFStar/' => ['https://www.jpxgyw.com/MFStar/page_1.html' => 'https://www.jpxgyw.com/MFStar/'], // MFStar模范学院
            '/Huayan/' => ['https://www.jpxgyw.com/Huayan/page_1.html' => 'https://www.jpxgyw.com/Huayan/'], // Huayan花の颜
            '/DKGirl/' => ['https://www.jpxgyw.com/DKGirl/page_1.html' => 'https://www.jpxgyw.com/DKGirl/'], // DKGirl御女郎
            '/Candy/' => ['https://www.jpxgyw.com/Candy/page_1.html' => 'https://www.jpxgyw.com/Candy/'], // Candy糖果画报
            '/YouMi/' => ['https://www.jpxgyw.com/YouMi/page_1.html' => 'https://www.jpxgyw.com/YouMi/'], // YouMi尤蜜荟
            '/MintYe/' => ['https://www.jpxgyw.com/MintYe/page_1.html' => 'https://www.jpxgyw.com/MintYe/'], // MintYe薄荷叶
            '/Micat/' => ['https://www.jpxgyw.com/Micat/page_1.html' => 'https://www.jpxgyw.com/Micat/'], // Micat猫萌榜
            '/Mtmeng/' => ['https://www.jpxgyw.com/Mtmeng/page_1.html' => 'https://www.jpxgyw.com/Mtmeng/'], // Mtmeng模特联盟
            '/HuaYang/' => ['https://www.jpxgyw.com/HuaYang/page_1.html' => 'https://www.jpxgyw.com/HuaYang/'], // HuaYang花漾Show
            '/XingYan/' => ['https://www.jpxgyw.com/XingYan/page_1.html' => 'https://www.jpxgyw.com/XingYan/'], // XingYan星颜社
        ];
    }

    /** 列表02 - 《采集》任务 */
    public function list_02()
    {

        foreach ($list_url as $key => $url0) {
            $page = 0;
            do {
                $page++;
                sleep(1);
                $run_time = 0 - microtime(true);
                $this->logs_init($this->_tag, $this->_tag_sub);
                // ------------------------------------


                $url = str_replace('{page}', $page, $url0);
                $url = $list_go[$key][$url] ? $list_go[$key][$url] : $url;
                $this->msg("\$url:{$url}");
                $c = \plugins\curl\http::file_get_contents_loop($url, 8);
                print_r(['$c' => $c]);
                $page_data = false;
                if ($c && strpos($c, '访问页面出错了') === false) {
                    $c1 = explode('</table>', explode('<td class=td6>', $c)[1])[0];
                    $c1 = explode('<div class="biank1">', $c1);

                    print_r($c1);
                    foreach ($c1 as $v1) {
                        if (strpos($v1, '"/UploadFile/pic') !== false) {
                            $url = explode(' ', explode('href=', $v1)[1])[0];
                            $pic_id0 = explode('/', $url)[1];
                            $pic_id1 = explode('.', explode('/', $url)[2])[0];
                            $pic_id = (int)str_replace($pic_id0, '', $pic_id1);

                            $rs = $this->_check($pic_id);
                            if (($this->_mode && ($this->_mode == \task\manage::mode_dateup || $this->_mode == \task\manage::mode_check)) || !$rs) {
                                $name = explode(' ', explode('title=', $v1)[1])[0];
                                $name = mb_convert_encoding($name, 'UTF-8', 'GBK');
                                $img = '';// explode('"',explode('data-img="',$v1)[1])[0];
                                $this->msg("\$pic_id:{$pic_id} -> \$url:{$url} \$name:{$name} \$img:{$img}");
                                if ($rs && $rs['pic_ext']) {
                                    $tags = json_decode($rs['pic_tag'], true);
                                    $pic_ext = json_decode($rs['pic_ext'], true);
                                    $time_add = $pic_ext['add_time'];
                                    //$pic_centent = json_decode($rs['pic_centent'],true);
                                    $insert = false;
                                } else {
                                    $url2 = $this->_url_root . $url;
                                    list($tags, $pic_centent, $time_add, $data) = $this->_wget_data($url2);
                                    $pic_ext = [
                                        'centent' => $pic_centent,
                                        'time' => $time_add,
                                        // 'cover'   => $img,
                                        'data' => $data
                                    ];
                                    $insert = true;
                                }
                                //
                                // $bind        = $this->_fields($pic_id,$name,$url,$pic_ext,[],$tags,'xgyw','');
                                $bind = $this->_fields_pics_v1($pic_id, $name, [], $pic_ext, $url, $tags, 0, 1, '', '', '', $this->_tag_sub, '', '', '', 0, 0, 0, 0, 0, 0, $time_add);
                                //
                                if ($insert) {
                                    $this->_insert($bind);
                                }
                                $this->_wget_pics($pic_id, $pic_ext);
                            }
                            $this->msg("\$pic_id:{$pic_id}");
                            $page_data = true;
                        }
                    }
                    if ($this->_mode == \task\manage::mode_dateup && $page > 2) {
                        $t = false;
                    } elseif ($page_data) {
                        $t = true;
                    } else {
                        $t = false;
                    }
                } else {
                    $t = false;
                }

                // ------------------------------------------------
                $run_time += microtime(true);
                $this->done_step($run_time);
            } while ($t);
        }
    }

    /** 列表03 - 《采集》任务 */
    public function list_03()
    {

    }

    /** 封面 - 《采集》任务 */
    public function cover()
    {

    }

    /** 数据01 - 《采集》任务 */
    public function data_01()
    {

    }

    /** 数据02 - 《采集》任务 */
    public function data_02()
    {

    }

    /** 数据03 - 《采集》任务 */
    public function data_03()
    {

    }

    /** 附件 - 《采集》任务 */
    public function data_attachment()
    {

    }

    /** 列表 - 《发布》任务 */
    public function post_site_data()
    {

    }

    /** 附件 - 《发布》任务 */
    public function post_site_attachment()
    {

    }


    protected function _data2($list_url, $list_go)
    {

    }


    protected function _wget_data($url)
    {
        $c = \plugins\curl\http::file_get_contents_loop($url, 8);
        $rs = [];
        $tags = [];
        $time_add = explode('</span>', explode('</IFRAME>', $c)[1])[0];
        $pic_centent = explode('</div>', explode('<div class="ina">', $c)[1])[0];

        $time_add = mb_convert_encoding($time_add, 'UTF-8', 'GBK');
        $pic_centent = mb_convert_encoding($pic_centent, 'UTF-8', 'GBK');

        $time_add = explode('：', $time_add)[1];


        $c1 = explode('</div>', explode('<div class="page">', $c)[1])[0];
        $c2 = explode('a><a', $c1);
        $urls = [];
        foreach ($c2 as $v) {
            $urls[] = explode('"', explode('"', $v)[1])[0];
        }
        array_shift($urls);
        array_pop($urls);

        // print_r($urls);

        $imgs = explode('src=', explode('</div>', explode('<div class=img>', $c)[1])[0]);
        foreach ($imgs as $img) {
            if (strpos($img, '.jpg') !== false) {
                $rs[] = explode('"', explode('"', $img)[1])[0];
            }
        }

        foreach ($urls as $url) {
            $this->msg("\$url:{$url}");
            $c = \plugins\curl\http::file_get_contents_loop($this->_url_root . $url, 8);
            $imgs = explode('src=', explode('</div>', explode('<div class=img>', $c)[1])[0]);
            foreach ($imgs as $img) {
                if (strpos($img, '.jpg') !== false) {
                    $rs[] = explode('"', explode('"', $img)[1])[0];
                }
            }
        }

        $rs2 = [$tags, $pic_centent, $time_add, $rs];

        print_r($rs2);

        return $rs2;
    }


    protected function _wget_pics(int $pic_id, array $pic_ext)
    {
        $data3 = [];
        foreach ($pic_ext['data'] as $k => $url) {
            $file = ($k + 1) . "-" . explode('/', $url)[4];
            $data3[] = ['file' => $file, 'url' => $url];

        }
        if ($pic_ext['cover']) {
            $data2 = ['cover' => $pic_ext['cover'], 'data' => $data3];
        } else {
            $data2 = ['data' => $data3];
        }

        // wget
        $this->_wget_pics_base($pic_id, $data2, $pic_ext);
    }
}
