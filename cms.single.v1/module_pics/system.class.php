<?php
namespace module_pics;

class system extends \v
{
	/** 首页 */
	public function index($mod)
	{
        $this->init_page('/',true,true,true,'',3600);

        $cls = 'index';
        require $this->require_file('index.html.php');
	}

    /** 热门排行 */
    public function p($mod)
    {
        // top  news  hot rec
        list($cls,$data_id,$page) = \pics::mod2cdp($mod);

        if($data_id){
            $this->_p_details($cls,$data_id,$page);
        }else{
            $this->_p_lists($cls,$page);
        }
    }

    /** 分类 */
    protected function _p_lists($cls, $page)
    {
        $cls2  = $cls?"{$cls}/":'';
        if($page > 0)
        {
            $this->init_page("/p/{$cls2}list_{$page}.html",true,true,true,'');
        }else
        {
            $this->init_page("/p/{$cls2}",true,true,true,'',3600);
        }

        $title_page  = \site_cfg::pics[$cls];
        if($title_page)
        {
            $mod_id                 = \site_cfg::mod_pics;
            $where_bind             = ['mod_id'=>$mod_id];
            if('' == $cls){
                $where              = ' where `mod_id` = :mod_id ';
                $navs               =['title'=> \site_cfg::pics['']  , 'url'=>'/p/'];
                $order_by           = ' order by `time_pub` ASC ';
            }elseif ('top' == $cls){
                $where              = ' where `mod_id` = :mod_id ';
                $navs               =['title'=> \site_cfg::pics['top'], 'url'=>'/p/top/'];
                $order_by           = ' order by `times` ASC ';
            }elseif ('news' == $cls){
                $where              = ' where `mod_id` = :mod_id ';
                $navs               =['title'=> \site_cfg::pics['news'],'url'=>'/p/news/'];
                $order_by           = ' order by `time_pub` ASC ';
            }elseif ('rec' == $cls){
                $where              = ' where `mod_id` = :mod_id ';
                $navs               =['title'=> \site_cfg::pics['rec'], 'url'=>'/p/rec/'];
                $order_by           = ' order by `scores` ASC ';
            }else{
                $category_id        = \site_cfg::maps[$cls];
                if($category_id){
                    $where          = ' where `mod_id` = :mod_id and category_id =:category_id ';
                    $navs           = ['title'=>\site_cfg::pics[$cls],'url'=>"/p/{$cls}/"];
                    $where_bind     = ['mod_id'=>$mod_id,'category_id'=>$category_id];
                }else{
                    $where          = ' where `mod_id` = :mod_id ';
                    $navs           =['title'=> \site_cfg::pics[''] ,'url'=>'/p/'];
                }
                $order_by           = ' order by `time_pub` ASC ';
            }

            //
            $rows               = 20;
            $url                = "/p/{$cls2}list_{page}.html";
            $table              = ' `data` ';
            $page_cfg           = \site_cfg::cfg_page;
            $page_cfg['index']  = ['/list_{total_page}.html','/'];
            $is_page_max        = true;

            require $this->require_file('pic_lists.html.php');
            exit;
        }
        \ounun::error404('');
    }

    /** 具本页面 */
    protected function _p_details($cls, $data_id, $page)
    {
        $data_id = (int)$data_id;
        if($page > 1)
        {
            $this->init_page("/p/{$cls}/{$data_id}_{$page}.html");
        }else
        {
            $page = 1;
            $this->init_page("/p/{$cls}/{$data_id}.html");
        }

        $pics_data  = self::$cms->pics_details($data_id);
        if($pics_data)
        {
            // print_r($pics_data);
            $category_cls = \site_cfg::maps[$pics_data['category_id']];
            if($cls == $category_cls)
            {
                $data_id       = $pics_data['data_id'];
                $category_id   = $pics_data['category_id'];
                $pic_tag       = $pics_data['tag'];
                $pic_title     = $pics_data['title'].($page<2?'':"({$page})");

                require $this->require_file('pic_details.html.php');
                exit;
            }
            \ounun::go_url("/p/{$category_cls}/{$data_id}.html");
        }
        \ounun::error404('');
    }

    /** 美女明星 */
    public function star($mod)
    {
        list($cls,$star_pinyin,$page) = \pics::mod2cdp($mod);

        // print_r(['$cls'=>$cls,'$star_pinyin'=>$star_pinyin,'$page'=>$page]);
        if($cls)
        {
            $star_pinyin = $cls;
            $cls         = '';
        }

        if($star_pinyin){
            $this->_star_details($cls,$star_pinyin,$page);
        }else{
            $this->_star_lists($cls,$page);
        }
    }

    protected function _star_lists($cls,$page)
    {
        $cls2  = $cls?"{$cls}/":'';
        if($page > 0)
        {
            $this->init_page("/star/{$cls2}list_{$page}.html",true,true,true,'');
        }else
        {
            $this->init_page("/star/{$cls2}",true,true,true,'',3600);
        }

        // $table,$where,$where_bind,$order_by,$rows,$url,$page,$page_cfg,$is_page_max
        $where_bind  = ['mod_id' => \site_cfg::mod_star];
        $where       = ' where `data` .`mod_id` = :mod_id and `data` .`data_id` = `data_star` .`star_id` ';
        $order_by    = ' order by `time_pub` ASC ';
        $table       = ' `data` ,`data_star` ';
        $fields      = ' `data` .`data_id`, `data` .`title`, `data` .`times`,`data` .`exts`, `data_star`.`pinyin` ';
        $is_page_max = true;

        $rows              = 20;
        $url               = "/star/{$cls2}list_{page}.html";
        $page_cfg          = \site_cfg::cfg_page;
        $page_cfg['index'] = ['/list_{total_page}.html', '/'];


        require $this->require_file('star_lists.html.php');
    }

    protected function _star_details($cls,$star_pinyin,$page)
    {
        $cls2  = $cls?"{$cls}/":'';
        if($page > 1)
        {
            $this->init_page("/star/{$cls2}{$star_pinyin}_{$page}.html");
        }else
        {
            $page = 1;
            $this->init_page("/star/{$cls2}{$star_pinyin}.html");
        }

        $star_data = \v::$cms->star_details($star_pinyin,'pinyin',' * , `data_star` .`exts` as `star_exts` ,  `data` .`exts` as `exts` ');
        if ($star_data && $star_data['title'])
        {
            require $this->require_file('star_details.html.php');
            exit();
        }
        \ounun::error404('');
    }

    /** 娱乐新闻 */
    public function news($mod)
    {
        //  yule  gossip
        list($cls,$news_id,$page) = \pics::mod2cdp($mod);

        if($news_id){
            $this->_news_details($cls,$news_id,$page);
        }else{
            $this->_news_lists($cls,$page);
        }
    }

    protected function _news_lists($cls,$page)
    {
        $cls2  = $cls?"{$cls}/":'';
        if($page > 0)
        {
            $this->init_page("/news/{$cls2}list_{$page}.html",true,true,true,'');
        }else
        {
            $this->init_page("/news/{$cls2}",true,true,true,'',3600);
        }

        $title_page  = \site_cfg::news[$cls];
        if($title_page)
        {
            $mod_id                 = \site_cfg::mod_news;
            $where_bind             = ['mod_id'=>$mod_id];
            if('' == $cls){
                $where              = ' where `mod_id` = :mod_id ';
                $navs               =['title'=> \site_cfg::news['']  , 'url'=>'/news/'];
                $order_by           = ' order by `time_pub` ASC ';
            }else{
                $category_id        = \site_cfg::maps[$cls];
                if($category_id){
                    $where          = ' where `mod_id` = :mod_id and category_id =:category_id ';
                    $navs           = ['title'=>\site_cfg::news[$cls],'url'=>"/news/{$cls}/"];
                    $where_bind     = ['mod_id'=>$mod_id,'category_id'=>$category_id];
                }else{
                    $where          = ' where `mod_id` = :mod_id ';
                    $navs           =['title'=> \site_cfg::news[''] ,'url'=>'/news/'];
                }
                $order_by           = ' order by `time_pub` ASC ';
            }

            //
            $rows               = 30;
            $url                = "/news/{$cls2}list_{page}.html";
            $table              = ' `data` ';
            $page_cfg           = \site_cfg::cfg_page;
            $page_cfg['index']  = ['/list_{total_page}.html','/'];
            $is_page_max        = true;

            require $this->require_file('news_lists.html.php');
            exit;
        }
        \ounun::error404('');
    }

    protected function _news_details($cls,$news_id,$page)
    {
        $cls2  = $cls?"{$cls}/":'';
        if($page > 1)
        {
            $this->init_page("/news/{$cls2}{$news_id}_{$page}.html");
        }else
        {
            $page = 1;
            $this->init_page("/news/{$cls2}{$news_id}.html");
        }

        $news_data  = self::$cms->pics_details($news_id);
        if($news_data)
        {
            // print_r($pics_data);
            $category_cls = \site_cfg::maps[$news_data['category_id']];
            if($cls == $category_cls)
            {
                $data_id       = $news_data['data_id'];
                $category_id   = $news_data['category_id'];
                $news_tag      = $news_data['tag'];
                $news_title    = $news_data['title'].($page<2?'':"({$page})");

                require $this->require_file('news_details.html.php');
                exit;
            }
            \ounun::go_url("/news/{$category_cls}/{$news_id}.html");
        }
        \ounun::error404('');
    }

    /** 专题 */
    public function special($mod)
    {

    }

    /** 我要上传 */
    public function up($mod)
    {

    }


    /** 标识 */
    public function tag($mod)
    {
        $tag = $mod[1];
        if($tag && 'list' != substr($tag,0,4))
        {
            $tag        = explode('_', $tag);
            $page       = (int)$tag[1];
            $tag_pinyin = $tag[0];
            if ($page > 0)
            {
                $this->init_page("/tag/{$tag_pinyin}_{$page}.html", true, true, true, '');
            } else
            {
                $this->init_page("/tag/{$tag_pinyin}.html", true, true, true, '', 3600);
            }

            $tag_data = \v::$cms->tags_p2n($tag_pinyin);
            if ($tag_data && $tag_data['tag'])
            {
                // $table,$where,$where_bind,$order_by,$rows,$url,$page,$page_cfg,$is_page_max
                $tag         = $tag_data['tag'];
                $where_bind  = ['mod_id' => \site_cfg::mod_pics,'tag'=> "%{$tag}%"];
                $where       = ' where `mod_id` = :mod_id and `tag` like :tag ';
                $order_by    = ' order by `time_pub` ASC ';
                $table       = ' `data` ';
                $is_page_max = true;

                $rows              = 20;
                $url               = "/tag/{$tag_pinyin}_{page}.html";
                $page_cfg          = \site_cfg::cfg_page;
                $page_cfg['index'] = ['/list_{total_page}.html', '/'];

                require $this->require_file('tag_lists.html.php');

            }else
            {
                \ounun::error404();
            }
        }else
        {
            $page = 0;
            if($mod[1]){
                list($list,$page) = explode('_',$mod[1]);
            }
            if($page > 0)
            {
                $this->init_page("/tag/list_{$page}.html",true,true,true,'');
            }else
            {
                $this->init_page("/tag/",true,true,true,'',3600);
            }

            $where_bind  = [];
            $where       = ' where `pinyin` != \'\' ';
            $order_by    = ' ORDER BY `official` ASC  ';
            $table       = ' `tag` ';
            $is_page_max = true;

            $rows              = 20;
            $url               = "/tag/list_{page}.html";
            $page_cfg          = \site_cfg::cfg_page;
            $page_cfg['index'] = ['/list_{total_page}.html', '/'];

            require $this->require_file('tag.html.php');
        }
    }

    /** 搜索 */
    public function search($mod)
    {
        $page          = 1;
        $tag           = $mod[1];
        if($tag) {
            $tag       = explode('_',$tag);
            $page      = (int)$tag[1];
            $tag       = $tag[0];
            $_GET['q'] = $tag;
        }else {
            $tag       = $_GET['q'];
        }
        $mod    = $mod[0];
        $mod_id = \site_cfg::mod_pics;
        $this->_search($tag,$page,$mod,$mod_id);
    }

    public function search_news($mod)
    {
        $page          = 1;
        $tag           = $mod[1];
        if($tag) {
            $tag       = explode('_',$tag);
            $page      = (int)$tag[1];
            $tag       = $tag[0];
            $_GET['q'] = $tag;
        }else {
            $tag       = $_GET['q'];
        }
        $mod    = $mod[0];
        $mod_id = \site_cfg::mod_news;
        $this->_search($tag,$page,$mod,$mod_id);
    }

    protected function _search($tag,$page,$mod,$mod_id)
    {
        if($tag)
        {
            if($page > 0)
            {
                $url_r = "/{$mod}/".urlencode($tag)."_{$page}.html";
                $this->init_page($url_r,true,true,true,'');
            }else
            {
                $url_r = "/{$mod}/".urlencode($tag).".html";
                $this->init_page($url_r,true,true,true,'',3600);
            }

            $rows              = 20;
            $table             = ' `data` ';
            $where             = ' WHERE `mod_id` = :mod_id  and (  `title` LIKE  :tag or `tag` LIKE  :tag ) ';
            $where_bind        = ['tag'=>"%{$tag}%",'mod_id'=> $mod_id];
            $order_by          = ' order by `time_pub` ASC ';
            $navs              = ['title'=> "搜索:{$tag}", 'url'=>$url_r];
            $url               = "/{$mod}/".urlencode($tag)."_{page}.html";
            $is_page_max       = true;
            $page_cfg          = \site_cfg::cfg_page;
            $page_cfg['index'] = ['_{total_page}.html','.html'];

            if($mod_id == \site_cfg::mod_news){
                require $this->require_file('news_lists.html.php');
            }else{
                require $this->require_file('pic_lists.html.php');
            }
        }else
        {
             \ounun::go_url('/tag/',false,302);
        }
    }

    /**
     * 广告
     * @param $mod array
     */
    public function m($mod)
    {
        echo 'var m_gcom='.json_encode(\app\ads::m )."\n";
        echo 'function adwrite(mode,size) 
{ 
    var show = false; 
    var str = \'mode:\'+mode+\' size:\'+size; 
    if(m_gcom && m_gcom[mode]) 
    { 
        show = true; 
        str = m_gcom[mode]; 
    } 
    document.write(str); 
}';
    }
}
