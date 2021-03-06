<?php
namespace cms;

class base
{
    /** @var  \ounun\mysqli */
    protected $_db;

    /** @var  \seo\base */
    protected $_seo;

    /** @var string 扩展名 */
    protected $_page_ext    = '.html';

    /** @var bool 首页 true(默认)：最大页 false:第一页 */
    protected $_page_is_max = true;


    /** base constructor. */
    public function __construct(\ounun\mysqli $db,\seo\base $seo,string $page_ext,bool $page_is_max)
    {
        $this->_db          = $db;
        $this->_seo         = $seo;
        $this->_page_ext    = $page_ext;
        $this->_page_is_max = $page_is_max;
    }

    /** @return \ounun\mysqli 输出db */
    public function db()
    {
        return $this->_db;
    }
    /**
     * @param array $data
     * @param int $len
     * @param string $ext
     * @return string
     */
    public function a(array $data, int $len=0, string $a_ext=''):string
    {
        $tag  = $len?\ounun\util::msubstr($data['title'],0,$len,true) :$data['title'];
        return "<a href=\"{$data['url']}\" title=\"{$data['title']}\" {$a_ext}>{$tag}</a>";
    }

    /**
     * @param  array  $url_list
     * @param  int    $len
     * @param  string $ext
     * @return array
     */
    public function a_m(array $urls, int $len=0):array
    {
        $rs       = [];
        foreach($urls as $v)
        {
            if($v['url'] && $v['title'])
            {
                $ext  = $v['ext']?$v['ext']:'';
                $rs[] = $this->a($v,$len,$ext);
            }
        }
        return $rs;
    }

    /**
     * @param array $urls
     * @param string $glue
     * @param int $len
     * @return string
     */
    public function a_s(array $urls,string $glue = "", int $len=0):string
    {
        $rs = $this->a_m($urls,$len);
        return implode($glue,$rs);
    }

    /**
     * @param $actor
     * @param int $len
     * @return array
     */
    public function kv2a_m(array $array, string $url_fun, $is_html=true):array
    {
        $rs       = [];
        foreach ($array as $id => $title)
        {
            $url  = $this->$url_fun($id);
            $rs[] = [
                'title' => $title,
                'url'   => $url
            ];
        }
        if($is_html)
        {
            $rs = $this->a_m($rs);
        }
        return $rs;
    }

    /**
     * 输出连接好的 html
     * @param array $array
     * @param string $url_fun
     * @param string $glue
     * @return string
     */
    public function kv2a_s(array $array, string $url_fun,string $glue = ""):string
    {
        $rs = $this->kv2a_m($array,$url_fun,true);
        return implode($glue,$rs);
    }

    /**
     * @param string $url
     * @param string $dir
     * @param int    $page
     * @return string
     */
    public function url(string $url,string $dir,int $page=1,int $page_max=0):string
    {
        if($this->_page_is_max)
        {
            $page     = $page > 1?$page:1;
            $page_str = $page >= $page_max?"":"-p{$page}";
        }else
        {
            $page_str = $page > 1?"-p{$page}":"";
        }
        return "/{$dir}/{$url}{$page_str}{$this->_page_ext}";
    }
}