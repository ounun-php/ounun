<?php

namespace extend\task\coll;

use ounun\cmd\task\coll_base\_coll;
use ounun\cmd\task\manage;

class me_99mm extends _coll
{

    public function __construct(manage $task_manage, string $tag = '', string $tag_sub = '')
    {
        parent::__construct($task_manage, $tag, $tag_sub);

        $this->_tag = 'pic';
        $this->_tag_sub = '99mm.me';
        $this->configs('http://www.99mm.me', '`pics_99mm`', '99mm', '/data/ossfs_io3/', 'libs_v1');
    }


    /**
     * php ~/Transcend/www/com.ygcms.mm.2015/index.php zrun_cmd,coll99mm
     * php /data/www_383434/index.php zrun_cmd,coll99mm
     * php /data/rbj_www.2015/index.php zrun_cmd,coll99mm,1
     * @param $mod
     */
    public function data()
    {
        // print_r($mod);
        // $is_check = (int)$mod[1];
        // $url_root = 'http://www.99mm.me';
        $list_url = [
            'meitui' => 'http://www.99mm.me/meitui/mm_1_{page}.html',
            'xinggan' => 'http://www.99mm.me/xinggan/mm_2_{page}.html',// http://www.99mm.me/xinggan/mm_2_2.html
            'qingchun' => 'http://www.99mm.me/qingchun/mm_3_{page}.html',
            'hot' => 'http://www.99mm.me/hot/mm_4_{page}.html',
        ];
        $list_go = [
            'meitui' => ['http://www.99mm.me/meitui/mm_1_1.html' => 'http://www.99mm.me/meitui/'],
            'xinggan' => ['http://www.99mm.me/xinggan/mm_2_1.html' => 'http://www.99mm.me/xinggan/'],
            'qingchun' => ['http://www.99mm.me/qingchun/mm_3_1.html' => 'http://www.99mm.me/qingchun/'],
            'hot' => ['http://www.99mm.me/hot/mm_4_1.html' => 'http://www.99mm.me/hot/'],
        ];

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

                $c = \plugins\curl\http::file_get_contents($url, $this->_url_root);
                if ($c && strpos($c, '您的访问出错了') === false) {
                    $c1 = explode('</ul>', explode('<ul id="piclist">', $c)[1])[0];
                    $c1 = explode('<li>', $c1);
                    foreach ($c1 as $v1) {
                        if (strpos($v1, 'img src') !== false) {
                            $url = explode('"', explode('<dt><a href="', $v1)[1])[0];
                            $pic_id = (int)explode('.', explode('/', $url)[2])[0];
                            $rs = $this->_check($pic_id);
                            if (($this->_mode && ($this->_mode == manage::mode_dateup || $this->_mode == manage::mode_check)) || !$rs) {
                                $name = explode('</a>', explode(' target="_blank">', $v1)[2])[0];
                                $img = explode('"', explode('data-img="', $v1)[1])[0];
                                $this->msg("\$pic_id:{$pic_id} -> \$url:{$url} \$name:{$name} \$img:{$img}");

                                $pic_ext = [
                                    'cover' => $img,
                                    'data' => $this->_wget_data($this->_url_root . $url, $img)
                                ];
                                // $bind   = $this->_fields($pic_id,$name,$url,$pic_ext,$this->_tag_sub);
                                // $bind   = $this->_fields($pic_id,$name,$url,$pic_ext,[],$tags,'mmjpg','');
                                // $bind   = $this->_fields($pic_id,$name,$url,$pic_ext,[],$tags,'mmjpg','');
                                $bind = $this->_fields_pics_v1($pic_id, $name, [], $pic_ext, $url, [], 0, 1, '', '', '', $this->_tag_sub);
                                // $bind   = $this->_fields(int $id,string $title,array $tags,array $data,array $data_origin,array $exts,string $origin_url,
                                //                          int $time_add = 0,int $coll_count=0,array $coll_exts=[],array $site=[],array $is=[],array $update=[]);
                                $this->_insert($bind);
                                $this->_wget_pics($pic_id, $pic_ext);
                            }
                        }
                    }
                    if ($this->_mode == manage::mode_dateup && $page > 2) {
                        $t = false;
                    } else {
                        $t = true;
                    }
                } else {
                    $t = false;
                }
                $run_time += microtime(true);
                $this->done_step($run_time);
            } while ($t);
        }
    }


    /**
     * @param string $url
     * @param string $img_url
     * @return array
     */
    protected function _wget_data(string $url, string $img_url)
    {
        $https = explode('/', $img_url)[0];
        $domain = explode('/', $img_url)[2];
        $c = \plugins\curl\http::file_get_contents($url, $this->_url_root);
        $str = explode('\';', explode('var iaStr = \'', $c)[1])[0];
        $str = str_replace(['%'], ',', strtolower($str));
        $str = explode(',', $str);
        // $host= ['img','file'];
        $rs = [];
        foreach ($str as $idx => $v) {
            if ($idx > 7) {
                // $rs[] = 'http://'.$host[$str[1]].'.99mm.net/'.$str[4].'/'.$str[5].'/'.($idx-7).'-'.$str[$idx].'.jpg';
                $rs[] = "{$https}//{$domain}/" . $str[4] . '/' . $str[5] . '/' . ($idx - 7) . '-' . $str[$idx] . '.jpg';
            }
        }

        return $rs;
        // print_r($rs);
        // http://img.99mm.net/2018/2846/1-vu.jpg
        // http://img.99mm.net/2018/2846/2-6j.jpg
        // http://img.99mm.net/2018/2846/3-9w.jpg
    }


    /**
     * @param int $pic_id
     * @param array $pic_ext
     */
    protected function _wget_pics(int $pic_id, array $pic_ext)
    {
        $data3 = [];
        foreach ($pic_ext['data'] as $url) {
            $file = explode('/', $url)[5];
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
