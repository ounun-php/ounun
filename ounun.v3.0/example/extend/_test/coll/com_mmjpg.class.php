<?php
namespace extend\task\coll;

class com_mmjpg extends \ounun\cmd\task\coll_base\_coll
{

    public function __construct(\task\manage $task_manage,string $tag='',string $tag_sub ='')
    {
        parent::__construct($task_manage,$tag,$tag_sub);

        $this->_tag      = 'pic';
        $this->_tag_sub  = 'mmjpg.com';
        $this->configs('http://www.mmjpg.com','`pics_jpg`','jpg','/data/ossfs_io3/','libs_v1');
    }

    public function data()
    {
        // http://www.mmjpg.com/mm/1390
        if(\task\manage::mode_dateup == $this->_mode)
        {
            $page = $this->_last_id();
            if($page >= 1)
            {
                $page = $page - 1;
            }
        }else
        {
            $page = 0;
        }
        // ==================================================
        do{
            sleep(1);
            $page++;
            $run_time  = 0 - microtime(true);
            $this->logs_init($this->_tag,$this->_tag_sub);
            // ------------------------------------


            $url = str_replace('{page}',$page,"http://www.mmjpg.com/mm/{page}");
            $this->msg("\$url:{$url}");
            $c   = \plugins\curl\http::file_get_contents($url,$this->_url_root);

            if($c && strpos($c,'您要访问的页面不存在') === false)
            {
                $pic_id  = $page;
                $rs      = $this->_check($pic_id);
                if(($this->_mode && ( $this->_mode == \task\manage::mode_dateup || $this->_mode == \task\manage::mode_check ))  || !$rs)
                {
                    $name = explode('</h2>',explode('<h2>',$c)[1])[0];
                    $img  = '';// explode('"',explode('data-img="',$v1)[1])[0];
                    $this->msg("\$pic_id:{$pic_id} -> \$url:{$url} \$name:{$name} \$img:{$img}");
                    if($rs && $rs['pic_ext'])
                    {
                        $tags        = json_decode($rs['pic_tag'],true);
                        $pic_ext     = json_decode($rs['pic_ext'],true);
                        $time_add    = $pic_ext['add_time'];
                        $insert      = false;
                    }else
                    {
                        list($tags,$time_add,$data) = $this->_wget_data($url,$c);
                        $pic_ext     = [  'data' => $data ];
                        // $pic_ext     = [ 'cover' => $img, 'data' => $data ];
                        $insert      = true;
                    }
               //   print_r(['$pic_ext'=>$pic_ext]);
                    $bind      = $this->_fields_pics_v1($pic_id,$name,[],$pic_ext,$url,$tags,0,1,'','','',$this->_tag_sub,'','','',0,0,0,0,0,0,$time_add);
               //   $bind      = $this->_fields($pic_id,$name,$url,$pic_ext,[],$tags,'mmjpg','');
                    if($insert)
                    {
                        $this->_insert($bind);
                    }
                    $this->_wget_pics($pic_id,$pic_ext);
                }
                $t = true;
            }else
            {
                $t = false;
            }
            // ------------------------------------------------
            $run_time += microtime(true);
            $this->done_step($run_time);
        }while($t);
    }


    protected function _wget_data($url,$c)
    {
        $rs          = [];
        // $tags_s
        $tags_s      = explode('</div>',explode('<div class="tags">',$c)[1])[0];
        $tags_s      = explode('<a',$tags_s);
        $tags        = [];
        foreach ($tags_s as $v)
        {
            if($v && strpos($v,'href=') !== false)
            {
                $tags[]= explode('<',explode('>',$v)[1])[0];
            }
        }
        //
        $time_add    = explode('</i>',explode('发表于: ',$c)[1])[0];
        //
        $total       = 1;
        $c1          = explode('全部图片',explode('id="page"',$c)[1])[0];
        $c2          = explode('</a>',$c1);
        foreach ($c2 as $v)
        {
            // echo "\$v:{$v}\n";
            if($v && strpos($v,'href') !== false)
            { // $page    = explode('"',explode('"',$v)[1])[0];
                $page = (int)explode('">',$v)[1];
                if($page && $page > $total)
                {
                    $total = $page;
                }
            }
        }

        $imgs        = explode('data-img=',explode('<div class="content" id="content">',$c)[1])[0];
        $rs[]        = explode('"',explode('src="',$imgs)[1])[0];

        for ($page = 2;$page <= $total;$page++)
        {
            $url2    = "{$url}/{$page}";
            $this->msg("\$url2:{$url2}");
            $c2      = \plugins\curl\http::file_get_contents($url2,$this->_url_root);
            $imgs    = explode('data-img=',explode('<div class="content" id="content">',$c2)[1])[0];
            $rs[]    = explode('"',explode('src="',$imgs)[1])[0];
        }
        // print_r($rs2);
        return [$tags,$time_add,$rs];
    }


    protected function _wget_pics(int $pic_id, array $pic_ext)
    {
        $data3       = [];
        foreach ($pic_ext['data'] as $k=>$url)
        {
            $file    = ($k+1)."-".explode('/',$url)[5];
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
