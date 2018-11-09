<?php
namespace module\article;

class system extends \v_75
{
	/** 首页 */
	public function index($mod)
	{
        $this->init_page('/',true,true,true,'',3600);

        $cls = 'index';
        require $this->require_file('index.html.php');
	}

    /** 文章列表 */
	protected function _article_list($mod,int $page = 0)
    {
        $url = "/".implode('/',$mod).'/';
        if($page > 0)
        {
            $this->init_page("{$url}list_{$page}.html",true,true,true,'');
        }else
        {
            $this->init_page("{$url}",true,true,true,'',3600);
        }

        // print_r(['$mod'=>$mod,'$url'=>$url]);
        if( 1 == count($mod) )
        {
            $mulu         = \site_cfg::mulu_maps()[$mod[0]];
            $category_id  = (int)$mulu['category_id'];
            $category_sub = 0;
        }elseif( 1 < count($mod) )
        {
            $mulu         = \site_cfg::mulu_maps()[$mod[1]];
            $category_id  = (int)$mulu['category_id'];
            $category_sub = (int)$mulu['category_sub'];
        }else
        {
            \ounun::error404('$mod eroor:'.implode(',',$mod));
        }
        if(!$mulu)
        {
            \ounun::error404('$mulu eroor:'.implode(',',$mulu));
        }

        $table              = '`data`';
        $url                = "{$url}list_{page}.html";
        $where              = ' where `mod_id` = '.\site_cfg::mod_news." and  `category_id` = {$category_id} ". ($category_sub?" and `category_sub` = {$category_sub} ":'');
        $where_bind         = [];
        $order_by           = ' order by `data_id` DESC ';
        $rows               = 20;
        $page_cfg           = \site_cfg::cfg_page;
        $page_cfg['index']  = ['/list_{total_page}.html','/'];
        $is_page_max        = true;
        list($rs,$ps)      = \v_75::$cms->lists($table,$where,$where_bind,$order_by,$rows,$url,$page,$page_cfg,$is_page_max);

        require $this->require_file('article_list.html.php');
        exit();
    }

    /** 文章内容 */
    protected function _article_details($mod)
    {
        $url = "/".implode('/',$mod).'.html';
        $this->init_page($url,false,true);
        // print_r(['$mod'=>$mod,'$url'=>$url]);

        require $this->require_file('article_details.html.php');
        exit();
    }


    public function tag($mod)
    {
        if($mod[1])
        {
            list($tag_id,$page) = explode('_',$mod[1]);
        }else
        {
            $tag_id  = 0;
            $page    = 0;
            require $this->require_file('article_tag.html.php');
            exit();
        }

        if($page > 0)
        {
            $this->init_page("/tag/{$tag_id}_{$page}.html",true,true,true,'');
        }else
        {
            $this->init_page("/tag/{$tag_id}.html",true,true,true,'',3600);
        }



        $table              = '`data`';
        $url                = "/tag/{$tag_id}_{page}.html";
        $where              = ' where `mod_id` = '.\site_cfg::mod_news." and  `category_id` = {$category_id} ". ($category_sub?" and `category_sub` = {$category_sub} ":'');
        $where_bind         = [];
        $order_by           = ' order by `data_id` DESC ';
        $rows               = 20;
        $page_cfg           = \site_cfg::cfg_page;
        $page_cfg['index']  = ["/{$tag_id}_{total_page}.html","/{$tag_id}.html"];
        $is_page_max        = true;
        list($rs0,$ps)      = \v_75::$cms->lists($table,$where,$where_bind,$order_by,$rows,$url,$page,$page_cfg,$is_page_max);

        require $this->require_file('article_list.html.php');
        exit();
    }

    /** 搜索 */
    public function search($mod)
    {
        print_r(['$mod'=>$mod]);
//        $page          = 1;
//        $tag           = $mod[1];
//        if($tag)
//        {
//            $tag       = explode('_',$tag);
//            $page      = (int)$tag[1];
//            $tag       = $tag[0];
//            $_GET['q'] = $tag;
//        }else
//        {
//            $tag       = $_GET['q'];
//        }
//        $mod    = $mod[0];
//        $mod_id = \site_cfg::mod_pics;
//        $this->_search($tag,$page,$mod,$mod_id);
    }


    public function __call($method, $args)
    {
        $mod  = $args[0];
        $mulu = \cfg\data\article::mulu[$mod[0]];
        if('article' == $mulu['type'])
        {
            $len = count($mod);
            if($len == 2 && 'list_' == substr($mod[1],0,5))
            {
                $page = (int) substr($mod[1],5);
                array_pop($mod);
                $this->_article_list($mod,$page);
            }elseif($len == 3 && 'list_' == substr($mod[2],0,5))
            {
                $page = (int) substr($mod[2],5);
                array_pop($mod);
                $this->_article_list($mod,$page);
            }elseif($len >= 3)
            {
                $this->_article_details($mod);
            }else
            {
                $this->_article_list($mod,0);
            }
        }
        \ounun::error404();
    }

}
