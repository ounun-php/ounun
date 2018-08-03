<?php
namespace module_pics;

class sitemap extends \v
{
    const rows  = 5000;

    public function __construct($mod)
    {
        header("Content-type:text/xml");
        parent::__construct($mod);
    }

    /**
     * 网站地址
     * @param $mod array
     */
    public function index($mod)
    {
        $this->init_page('/sitemap/',true,true,true,'',86400);

        $total      = $this->_db_v->row("SELECT count(*) as cc FROM `sitemap`;");
        $total      = (int)$total['cc'];
        $total_page = ceil($total   / self::rows );
        $urls       = [];
        $date       = date('Y-m-d',time());
        for ($page =1;$page <= $total_page;$page++){
            $urls[] = ['lastmod'=>$date,'loc'=> "https://{$_SERVER['HTTP_HOST']}/sitemap/list2/{$page}.xml"];
            $urls[] = ['lastmod'=>$date,'loc'=> "https://{$_SERVER['HTTP_HOST']}/sitemap/list2/{$page}-m.xml"];
        }

        $xml     = $this->_index($urls);
        exit($xml);
    }

    /**
     * 特别URL 路由
     */
    public function list2($mod)
    {
        list($page,$m) = explode('-',$mod[1]);

        $this->init_page($m?"/sitemap/list2/{$page}-m.xml":"/sitemap/list2/{$page}.xml",true,true,true,'',86400);

        $page          = (int)$page;
        $page          = $page < 1?1:$page;
        $start         = ($page - 1) * self::rows;
        $rows          = self::rows;
        $rs            = $this->_db_v->data_array("SELECT * FROM `sitemap` ORDER BY `lastmod` DESC limit {$start},{$rows};");
        $urls          = [];
        foreach ($rs as $v){
            $urls[]    =  ['loc'=> Const_Url_Www.substr($v['loc'],1),'priority'=>0.0+$v['weight'], 'lastmod'=>date('Y-m-d',$v['lastmod']),'changefreq'=>$v['changefreq']];
        }

        $xml     = $this->_pc($urls);
        if('m' == $m)
        {
            $xml = $this->_mobile(Const_Url_Mobile,Const_Url_Www,$xml);
        }
        exit($xml);
    }

    /**
     * @param $sitemaps
     * @return string
     */
    protected function _index($sitemaps)
    {
        return '<?xml version="1.0" encoding="utf-8"?'.'>'."\n"
            .'<sitemapindex>'."\n"
            .\ounun\xml::array2xml($sitemaps,'sitemap').'</sitemapindex>';
    }

    /**
     * 生成sitemap
     * @param $data [array('loc'=>'http://www.383434.com','priority'=>'1.00', 'lastmod'=>'2015-03-23','changefreq'=>'daily')]
     * @return string
     */
    protected function _pc($data)
    {
        $urls = [];
        foreach ($data as $v){
            $urls[] = "<url><loc>{$v['loc']}</loc><lastmod>{$v['lastmod']}</lastmod><changefreq>{$v['changefreq']}</changefreq><priority>{$v['priority']}</priority></url>";
        }
        return '<?xml version="1.0" encoding="UTF-8"?'.'>'."\n"
            .'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:mobile="http://www.baidu.com/schemas/sitemap-mobile/1/">'."\n"
            .implode('',$urls).'</urlset>';
    }

    /**
     * 转成 移动sitemap
     * @param $host_m
     * @param $host_pc
     * @param $xml
     * @return mixed
     */
    protected function _mobile($host_m,$host_pc,$xml)
    {
        return str_replace(['</loc>',$host_pc],['</loc><mobile:mobile type="mobile" />',$host_m],$xml);
    }

}