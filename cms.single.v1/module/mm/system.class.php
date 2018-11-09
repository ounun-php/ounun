<?php
namespace module\mm;

class system extends \v_mm
{
	/**
	 * 首页
	 * @param $mod array       	
	 */
	public function index($mod)
	{
        $this->init_page('/',true,true,true,'',3600);

        $pic_class             = 'index';
        require $this->require_file('index.html.php');
	}

    /**
     * 热门排行
     */
    public function top($mod)
    {
        $page    = (int)str_replace('index_','',$mod[1]);
        if($page > 0)
        {
            $this->init_page("/top/index_{$page}.html",true,true,true,'');
        }else
        {
            $this->init_page("/top/",true,true,true,'',3600);
        } 

        $rows         = 16;
        $table        = '`pic_data`';
        $where        = ['where'=> ' ','bind' => ''];
        $order_by     = ' order by `pic_times` ASC ,`add_time` ASC ';
        $url          = \ounun::url_original("/top/index_{page}.html");
        $page_cfg     = \mm_pics::pics_page_cfg;
        $title_page   = '热门排行';
        $page_max     =  true;

        require $this->require_file('list_new.html.php');
    }
    /**
     * 站长推荐
     */
    public function hot($mod)
    {
        $page    = (int)str_replace('index_','',$mod[1]);
        if($page > 0)
        {
            $this->init_page("/hot/index_{$page}.html",true,true,true,'');
        }else
        {
            $this->init_page("/hot/",true,true,true,'',3600);
        }

        $rows         = 16;
        $table        = '`pic_data`';
        $where        = ['where'=> ' ','bind' => ''];
        $order_by     = ' order by `is_hot` ASC ,`add_time` ASC  ';
        $url          = \ounun::url_original("/hot/index_{page}.html");
        $page_cfg     = \mm_pics::pics_page_cfg;
        $title_page   = '站长推荐';
        $page_max     =  true;

        require $this->require_file('list_new.html.php');
    }
    /**
     * 高清美女
     */
    public function gaoqing($mod)
    {
        // $db      = $this->db('core');
        $page    = (int)str_replace('index_','',$mod[1]);

        if($page > 0)
        {
            $this->init_page("/gaoqing/index_{$page}.html",true,true,true,'');
        }else
        {
            $this->init_page("/gaoqing/",true,true,true,'',3600);
        }

        $rows         = 16;
        $table        = '`pic_data`';
        $where        = ['where'=> ' ','bind' => ''];
        $order_by     = ' order by `is_gaoqing` ASC ,`add_time` ASC ';
        $url          = \ounun::url_original("/gaoqing/index_{page}.html");
        $page_cfg     = \mm_pics::pics_page_cfg;
        $title_page   = '高清美女';
        $page_max     =  true;

        require $this->require_file('list_new.html.php');
    }
    /**
     * 最新更新
     */
    public function new2($mod)
    {
        $page    = (int)str_replace('index_','',$mod[1]);
        if($page > 0)
        {
            $this->init_page("/new2/index_{$page}.html",true,true,true,'');
        }else
        {
            $this->init_page("/new2/",true,true,true,'',3600);
        }

        $rows         = 16;
        $table        = '`pic_data`';
        $where        = ['where'=> ' ','bind' => ''];
        $order_by     = ' order by `add_time` ASC ';
        $url          = \ounun::url_original("/new2/index_{page}.html");
        $page_cfg     = \mm_pics::pics_page_cfg;
        $title_page   = '最新更新';
        $page_max     =  true;

        require $this->require_file('list_new.html.php');
    }
    /**
     * 最新更新
     */
    public function search($mod)
    {
        $tag   = $mod[1];
        $page  = 0;
        if($tag)
        {
            $tag     = explode('_',$tag);
            $page    = (int)$tag[1];
            $tag     = $tag[0];
            $_GET['q'] = $tag;
        }else
        {
            $tag   = $_GET['q'];
        }

        if($tag)
        {
            if($page > 0)
            {
                $this->init_page("/search/".urlencode($tag)."_{$page}.html",true,true,true,'');
            }else
            {
                $this->init_page("/search/".urlencode($tag).".html",true,true,true,'',3600);
            }

            $rows              = 18;
            $table             = '`pic_data`';
            $where             = [ 'where'=> ' WHERE  `pic_title` LIKE  ? or `pic_tag` LIKE  ? ','bind' => "%{$tag}%" ];
            $order_by          = ' order by `add_time` ASC ';
            $url               = \ounun::url_original("/search/".urlencode($tag)."_{page}.html");
            $page_cfg          = \mm_pics::pics_page_cfg;
            $page_cfg['index'] = ['_{total_page}.html','.html'];
        }else
        {
             \ounun::go_url('/tag/',false,302);
        }

        $tag                   = "搜索:".$tag;
        $pic_class             = '';
        $is_search             = true;

        require $this->require_file('tag_details.html.php');
    }
    /**
     * 标识
     */
    public function tag($mod)
    {
        $tag = $mod[1];
        if($tag)
        {
            $tag     = explode('_',$tag);
            $page    = (int)$tag[1];
            $tag     = $tag[0];
            if($page > 0)
            {
                $this->init_page("/tag/".urlencode($tag)."_{$page}.html",true,true,true,'');
            }else
            {
                $this->init_page("/tag/".urlencode($tag).".html",true,true,true,'',3600);
            }

            $pic_class          = '';
            $is_search          = false;

            $rows               = 18;
            $url                = \ounun::url_original("/tag/".urlencode($tag)."_{page}.html");
            $page_cfg           = \mm_pics::pics_page_cfg;
            $page_cfg['index']  = ['/index_{total_page}.html','/'];

            require $this->require_file('tag_details.html.php');
        }else
        {
            $this->init_page("/tag/");

            require $this->require_file('tag.html.php');
        }
    }
    /**
     * 分类
     */
    private function _class($pic_class,$page)
    {
        if($page > 0)
        {
            $this->init_page("/{$pic_class}/index_{$page}.html",true,true,true,'');
        }else
        {
            $this->init_page("/{$pic_class}/",true,true,true,'',3600);
        }

        $title_page  = \mm_pics::pics_class[$pic_class];
        if($title_page)
        {
            $rows               = 18;
            $url                = \ounun::url_original("/{$pic_class}/index_{page}.html");
            $table              = '`pic_data`';
            $where              = ['where'=> ' where `pic_class` = ? ','bind' => $pic_class];
            $order_by           = ' order by `add_time` ASC ';
            $page_cfg           = \mm_pics::pics_page_cfg;
            $page_cfg['index']  = ['/index_{total_page}.html','/'];
            $page_max           = true;

            require $this->require_file('list.html.php');
            exit;
        }
        return array(false,404);
    }
    /**
     * 具本页面
     */
    private function _details($pic_class,$pic_id,$pic_page)
    {
        if($pic_page > 1)
        {
            $this->init_page("/{$pic_class}/{$pic_id}_{$pic_page}.html");
        }else
        {
            $this->init_page("/{$pic_class}/{$pic_id}.html");
        }

        $rs  = self::$cms->pics_details($pic_id);
        if($rs)
        {
            if($pic_class == $rs['pic_class'])
            {
                $this->_details_page($rs,$pic_page);
                exit;
            }
            return array(false,301,"/{$rs['pic_class']}/{$pic_id}.html");
        }
        return array(false,404);
    }

    /**
     * 具本页面
     */
    private function _details_page($pic_data,$pic_page)
    {

        $pic_id        = $pic_data['pic_id'];
        $pic_class     = $pic_data['pic_class'];
        $pic_tag       = $pic_data['pic_tag'];
        $pic_title     = $pic_data['pic_title'].($pic_page<2?'':"({$pic_page})");

        require $this->require_file('details.html.php');
    }

    /**
     * 特别URL 路由
     */
    public function __call($method, $args)
    {
        $mod      = $args[0];
        $pic_id   = explode('_',$mod[1]);
        $pic_page = (int)$pic_id[1];
        $pic_id   = (int)$pic_id[0];

        if($pic_id && 2 == count($mod))
        {
            $pic_page= $pic_page<1?1:$pic_page;
            $rs      = $this->_details($method,$pic_id,$pic_page);
            if(false == $rs[0] && 301 == $rs[1])
            {
                \ounun::go_url($rs[2],false,301);
            }
        }elseif( $mod[0] && \mm_pics::pics_class[$mod[0]] )
        {
            $page = (int)str_replace('index_','',$mod[1]);
            $this->_class($mod[0],$page);
        }
        \ounun::error404();
    }




//    public function video($mod)
//    {
//        $this->init_page("/video.html");
//
//        require $this->require_file('video.html.php');
//    }


}
