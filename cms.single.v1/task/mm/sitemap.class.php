<?php
namespace task\mm;

use ounun\logs;

class sitemap extends \task\base_system_sitemap
{
    /**
     * 刷新 sitemap
     * @param $mod
     */
    public function url_refresh()
    {
        // 列表
        $bind = $this->_data('/','index','daily',0,1);
        $this->_insert($bind,true);

//        $this->_url_pics();
//        $this->_url_news();
//        $this->_url_tags();
//        $this->_url_star();
        $this->_url_refresh2();
    }


    /**
     * 
     * php ~/Transcend/www/com.ygcms.mm.2015/index.php zrun_api,url_refresh  mm.erldoc.com
     * php ~/Transcend/www/com.ygcms.mm.2015/index.php zrun_api,url_refresh www.383434.com
     *
     * php /data/rbj_www.2015/index.php zrun_api,url_refresh  mm.erldoc.com
     * php /data/rbj_www.2015/index.php zrun_api,url_refresh www.383434.com
     *
     * 数据接口  输出接口
     *
     * @param $mod
     */
    protected function _url_refresh2()
    {
        // 分类
        foreach (\mm_pics::pics_class as $pic_class => $class_name)
        {
            // 分类列表 首页
            $bind       = $this->_data( "/{$pic_class}/",'pics_cls_index','daily',0,1);
            $this->_insert($bind,true);
            // 分类列表 内页
            $bind       = [];
            $rs         = $this->_db->row("SELECT count(`pic_id`) as cc FROM `pic_data` where `pic_class` = ? ;",$pic_class);
            $count      = $rs['cc'];
            $total_page = ceil($count   / 18);
            $this->msg("tag:{$pic_class} count:{$count} class_name:{$class_name} total_page:{$total_page}",logs::state_normal);
            if($total_page > 1)
            {
                $url0       = "/{$pic_class}/index_{page}.html";
                for ($page =1;$page < $total_page;$page++){
                    $url    = str_replace('{page}',$page,$url0);
                    $bind[] = $this->_data( $url,'pics_cls_list','weekly',0,0.8);
                }
            }
            $this->_insert($bind,false);
        }

        // 热门 最新
        $top_list = ['new2','gaoqing','hot','top'];
        $rs       = $this->_db->row("SELECT count(`pic_id`) as cc FROM `pic_data` ;");
        $count    = $rs['cc'];
        foreach ($top_list as $pic_class)
        {
            // 分类列表 首页
            $url_root   = "/{$pic_class}/";
            $bind       = $this->_data( $url_root,'pics_top_index','daily',0,1);
            $this->_insert($bind,true);

            // 分类列表 内页
            $bind       = [];
            $total_page = ceil($count   / 16);
            $this->msg("tag:{$pic_class}  count:{$count} total_page:{$total_page}",logs::state_normal);
            if($total_page > 1)
            {
                $url0     = "/{$pic_class}/index_{page}.html";
                for ($page =1;$page < $total_page;$page++){
                    $url    = str_replace('{page}',$page,$url0);
                    $bind[] = $this->_data( $url,'pics_top_list','weekly',0,0.8);
                }
            }
            $this->_insert($bind,false);
        }

        // tag   $tag  = $v['tag'];  $tag_count = $v['tag_count'];
        $tags  = $this->_db->data_array("SELECT `tag_id`,`tag`,`tag_count` FROM  `pic_tag` where  `tag_count` >= 5  ORDER BY `tag_count` DESC;");
        foreach ($tags as $v)
        {
            // tag 首页
            $bind       = $this->_data( "/tag/".urlencode($v['tag']).".html",'tag_index','daily',0,1);
            $this->_insert($bind,true);

            // tag 内页
            $bind       = [];
            $rs         = $this->_db->row("SELECT count(`pic_id`) as cc FROM `pic_tag_data` where `tag_id` = :tag_id  ;",$v);
            $count      = $rs['cc'];
            $total_page = ceil($count   / 18);
            // echo "\$pic_class:{$pic_class} \$count:{$count} \$tag:{$v['tag']} \$total_page:{$total_page}\n";
            if($total_page > 1)
            {
                $url0       = "/tag/".urlencode($v['tag'])."_{page}.html";
                for ($page =1;$page < $total_page;$page++){
                    $url    = str_replace('{page}',$page,$url0);
                    $bind[] = $this->_data( $url,'tag_list','weekly',0,0.7);
                }
            }
            $this->_insert($bind,false);
        }

        // search  $tag = $v['tag'];    $tag_count = $v['tag_count'];
        foreach ($tags as $v)
        {
            // tag 首页
            $bind       = $this->_data( "/search/".urlencode($v['tag']).".html",'search_index','daily',0,0.8);
            $this->_insert($bind,true);


            $bind       = [];
            $rs         = $this->_db->row("SELECT count(`pic_id`) as cc FROM `pic_data` WHERE  `pic_title` LIKE  ? or `pic_tag` LIKE  ?  ;", "%{$v['tag']}%");
            // echo $db->sql()."\n";
            $count      = $rs['cc'];
            $total_page = ceil($count   / 18);
            // echo "\$pic_class:{$pic_class} \$count:{$count} \$tag:{$v['tag']} \$total_page:{$total_page}\n";
            if($total_page > 1)
            {
                $url0       = "/search/".urlencode($v['tag'])."_{page}.html";
                for ($page =1;$page < $total_page;$page++){
                    $url    = str_replace('{page}',$page,$url0);
                    $bind[] = $this->_data( $url,'search_list','weekly',0,0.7);
                }
            }
            $this->_insert($bind,false);
        }
        // pic
        $pic_id    = 0;
        do{
            $rss =  $this->_db->data_array("SELECT * FROM  `pic_data`  where `pic_id` > :pic_id order by `pic_id` asc limit 0,20;",['pic_id'=>$pic_id]);
            // echo $db->sql()."\n";
            if($rss)
            {
                $bind    = [];
                foreach ($rss as $rs)
                {
                    $pic_ext     = unserialize($rs['pic_ext']);
                    $page_total  = count($pic_ext);
                    for ($pic_page=1;$pic_page<=$page_total;$pic_page++)
                    {
                        if($pic_page > 1 )
                        {
                            $bind[] = $this->_data( "/{$rs['pic_class']}/{$rs['pic_id']}_{$pic_page}.html",'pics_page','weekly',0,0.9);
                        }else
                        {
                            $bind[] = $this->_data(  "/{$rs['pic_class']}/{$rs['pic_id']}.html",'pics_index','weekly',1,1);
                        }
                    }
                    // echo "\$rs['pic_id']:{$rs['pic_id']}   \$page_total:".$page_total."\n";
                }
                // $db->insert(self::table_sitemap, $bind);
                $this->_insert($bind,false);
                $pic_id = (int)$rs['pic_id'];
            }else
            {
                // echo " <-- end\n\n";
                $pic_id = 0;
            }
        }while($pic_id);

    }
}
