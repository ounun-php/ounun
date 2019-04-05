<?php
namespace extend\task\coll;


class com_mm131 extends \ounun\cmd\task\coll_base\_coll
{

    public function __construct(\task\manage $task_manage,string $tag='',string $tag_sub ='')
    {
        parent::__construct($task_manage,$tag,$tag_sub);

        $this->_tag      = 'pic';
        $this->_tag_sub  = 'mm131.com';
        $this->configs('http://www.mm131.com','`pics_mm131`','131','/data/ossfs_io3/','libs_v1');
    }

    public function data()
    {
        $list_url = [
            'mingxing' => 'http://www.mm131.com/mingxing/list_5_{page}.html',
            'xinggan'  => 'http://www.mm131.com/xinggan/list_6_{page}.html',
            'qingchun' => 'http://www.mm131.com/qingchun/list_1_{page}.html',
            'xiaohua'  => 'http://www.mm131.com/xiaohua/list_2_{page}.html',
            'chemo'    => 'http://www.mm131.com/chemo/list_3_{page}.html',
            'qipao'    => 'http://www.mm131.com/qipao/list_4_{page}.html',
        ];
        $list_go  = [
            'mingxing' => ['http://www.mm131.com/mingxing/list_5_1.html'=>'http://www.mm131.com/mingxing/'],
            'xinggan'  => ['http://www.mm131.com/xinggan/list_6_1.html' =>'http://www.mm131.com/xinggan/'],
            'qingchun' => ['http://www.mm131.com/qingchun/list_1_1.html'=>'http://www.mm131.com/qingchun/'],
            'xiaohua'  => ['http://www.mm131.com/xiaohua/list_2_1.html' =>'http://www.mm131.com/xiaohua/'],
            'chemo'    => ['http://www.mm131.com/chemo/list_3_1.html'   =>'http://www.mm131.com/chemo/'],
            'qipao'    => ['http://www.mm131.com/qipao/list_4_1.html'   =>'http://www.mm131.com/qipao/'],
        ];

        foreach ($list_url as $key=>$url0)
        {
            $page = 0;
            do
            {
                $page++;
                sleep(1);
                $run_time  = 0 - microtime(true);
                $this->logs_init($this->_tag,$this->_tag_sub);
                // ------------------------------------

                $url = str_replace('{page}',$page,$url0);
                $url = $list_go[$key][$url]?$list_go[$key][$url]:$url;

                $c   = \plugins\curl\http::file_get_contents_loop($url,5);
                $this->msg("\$url:{$url}");
                if($c && strpos($c,'页面没有找到') === false)
                {
                    $c1 = explode('</dl>',explode('<div class="main">',$c)[1])[0];
                    $c1 = explode('<dd>',$c1);
                    foreach ($c1 as $v1)
                    {
                        if(strpos($v1,'img src') !== false)
                        {
                            $url    =      explode('"',explode('"_blank" href="',$v1)[1])[0];
                            $pic_id = (int)explode('.',explode('/',$url)[4])[0];
                            $rs     = $this->_check($pic_id);
                            if(($this->_mode && ( $this->_mode == \task\manage::mode_dateup || $this->_mode == \task\manage::mode_check ))  || !$rs)
                            {
                                $name = explode('"',explode('alt="',$v1)[1])[0];
                                $name = mb_convert_encoding($name,'UTF-8','GBK');
                                $img  = '';// explode('"',explode('data-img="',$v1)[1])[0];
                                $this->msg("\$pic_id:{$pic_id} -> \$url:{$url} \$name:{$name} \$img:{$img}");
                                if($rs && $rs['pic_ext'])
                                {
                                    $pic_ext = json_decode($rs['pic_ext'],true);
                                }else
                                {
                                 // $pic_ext = [ 'cover' => $img,  'data'  => $this->_get_data($url) ];
                                    $pic_ext = [  'data'  => $this->_get_data($url) ];
                                }
                                // $bind   = $this->_fields($pic_id,$name,$url,$pic_ext,'mm131');
                                $bind      = $this->_fields_pics_v1($pic_id,$name,[],$pic_ext,$url,[],0,1,'','','',$this->_tag_sub,'','','',0,0,0,0,0,0,0);

                                $this->_insert($bind);
                                $this->_wget_pics($pic_id,$pic_ext);
                            }
                        }
                    }
                    if( $this->_mode == \task\manage::mode_dateup && $page > 2)
                    {
                        $t = false;
                    }else
                    {
                        $t = true;
                    }
                }else
                {
                    $t = false;
                }
                $run_time += microtime(true);
                $this->done_step($run_time);
            }while($t);
        }
    }


    protected function _get_data($url)
    {
        $pic_id = (int)explode('.',explode('/',$url)[4])[0];
        $dir    =      explode('/',$url)[3];
        // echo "\$url 1:{$url}\n";
        $c      =  \plugins\curl\http::file_get_contents($url,$url);

        $c      =      explode('<div class="otherpic">',explode('<div class="content-pic">',$c)[1])[0];
        $rs     = [];
        $rs[]   =      explode('"',explode('src="',$c)[1])[0];
        $total  = (int)explode('页',explode('共',mb_convert_encoding($c,'UTF-8','GBK'))[1])[0];

        // echo "\$total :{$total}\n";
        for ($page = 2;$page <= $total;$page++)
        {
            $url2   = "{$this->_url_root}/{$dir}/{$pic_id}_{$page}.html";
            $this->msg("\$url {$page}:{$url2}");
            $c      = \plugins\curl\http::file_get_contents_loop($url2,10);
            $c      = explode('<div class="otherpic">',explode('<div class="content-pic">',$c)[1])[0];
            $rs[]   = explode('"',explode('src="',$c)[1])[0];
        };

        return $rs;
    }


    /**
     * @param int   $pic_id
     * @param array $pic_ext
     */
    protected function _wget_pics(int $pic_id, array $pic_ext)
    {
        $data3       = [];
        foreach ($pic_ext['data'] as $url)
        {
            $file    = explode('/',$url)[5];
            $data3[] = ['file'=>$file,'url'=>$url];

        }
        if($pic_ext['cover'])
        {
            $data2   = ['cover'=>$pic_ext['cover'],'data'=>$data3];
        }else
        {
            $data2   = ['data'=>$data3];
        }

        // wget
        $this->_wget_pics_base($pic_id,$data2,$pic_ext);
    }
}