<?php

namespace ounun\mvc\controller;

class sitemap extends \v
{
    /**
     * 网站地址
     * @param $mod array
     */
    public function index($mod)
    {
        header("Content-type:text/xml");
        $this->init_page('/sitemap/index.xml', true, true, true, '', 86400);

        $url_root = "sitemap/list/";
        $xml = $this->maps_index($url_root, $_SERVER['HTTP_HOST']);
        exit($xml);
    }

    /**
     * 特别URL 路由
     */
    public function list($mod)
    {
        header("Content-type:text/xml");
        $page = (int)$mod[1];
        $this->init_page("/sitemap/list/{$page}.xml", true, true, true, '', 86400);
        $xml = $this->maps_page($page, $_SERVER['HTTP_HOST']);
        exit($xml);
    }


    /**
     * 网站地址
     * @param string $url_root
     * @param string $host
     * @return string
     */
    protected function maps_index($url_root = 'sitemap/list/', string $host = '')
    {
        $total = static::$db_v->query("SELECT count(`url_id`) as `cc` FROM {$this->_table_sitemap};")->column_one();
        $total = (int)$total['cc'];
        $total_page = ceil($total / com_baidu::max_sitemaps_page);

        $url_root2 = $this->_maps_url($host);
        $urls = [];
        $date = date('Y-m-d', time());
        for ($page = 1; $page <= $total_page; $page++) {
            $urls[] = ['lastmod' => $date, 'loc' => "{$url_root2}/{$url_root}{$page}.xml"];
        }
        $xml = $this->_maps_index($urls);
        return $xml;
    }

    /**
     * 特别URL 路由
     * @param int $page
     * @param string $host
     * @return mixed|string
     */
    protected function maps_page(int $page = 1, string $host = '')
    {
        $page = $page < 1 ? 1 : $page;
        $start = ($page - 1) * com_baidu::max_sitemaps_page;
        $rows = com_baidu::max_sitemaps_page;
        $rs = $this->_db_site->query("SELECT * FROM {$this->_table_sitemap} ORDER BY `lastmod` DESC ,`url_id` ASC limit {$start},{$rows};")->column_all();
        // echo $this->_db->sql()."<br />";
        $url_root = $this->_maps_url($host);
        $urls = [];
        foreach ($rs as $v) {
            $urls[] = [
                'loc' => $url_root . $v['loc'],
                'priority' => 0.0 + $v['weight'],
                'lastmod' => date('Y-m-d', $v['lastmod']),
                'changefreq' => $v['changefreq']
            ];
        }
        // print_r($urls);
        $xml = $this->_maps_page($urls);
        if ($this->_domain_mip == $host && $this->_domain_wap == $host) {
            $xml = str_replace('</loc>', '</loc><mobile:mobile type="mobile" />', $xml);
        }
        return $xml;
    }

    /**
     * @param mixed $sitemaps
     * @return string
     */
    protected function _maps_index($sitemaps)
    {
        return '<?xml version="1.0" encoding="utf-8"?' . '>' . "\n"
            . '<sitemapindex>' . "\n"
            . \ounun\tool\data::array2xml($sitemaps, 'sitemap') . '</sitemapindex>';
    }

    /**
     * 生成sitemap
     * @param mixed $urls_data [array('loc'=>'http://www.383434.com','priority'=>'1.00', 'lastmod'=>'2015-03-23','changefreq'=>'daily')]
     * @return string
     */
    protected function _maps_page($urls_data)
    {
        $urls = [];
        foreach ($urls_data as $v) {
            $urls[] = "<url><loc>{$v['loc']}</loc><lastmod>{$v['lastmod']}</lastmod><changefreq>{$v['changefreq']}</changefreq><priority>{$v['priority']}</priority></url>";
        }
        return '<?xml version="1.0" encoding="UTF-8"?' . '>' . "\n"
            . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:mobile="http://www.baidu.com/schemas/sitemap-mobile/1/">' . "\n"
            . implode('', $urls)
            . '</urlset>';
    }
}
