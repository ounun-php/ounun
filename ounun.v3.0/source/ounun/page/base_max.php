<?php
/**
 * 主要功能: 分頁,有問題問我吧,沒時間寫注
 *
 * dreamxyp(QQ:31996798) - Page.class.php
 * coding:夏一平
 * 創建時間:2006-10-30
 * @example
 */
namespace ounun\page;

class base_max
{
    /** @var string  提示串 */
    protected $_config_note = '总共有{total}条数据,共{total_page}页,第{page}页';
    /** @var array   默认页 */
    protected $_config_page_tag_default = ['<li>', '</li>',''];
    /** @var array   当前页面时 */
    protected $_config_page_tag_curr = ['<li class="now">', '</li>',''];
    /** @var array   第一页 上一页 下一页 最后一页   ['|&lt;','&lt;','&gt;','&gt;|']; */
    protected $_config_page_tag_name = ['第一页', '上一页', '下一页', '最后一页'];
    /** @var int     最多显示几页 */
    protected $_config_show_max = 9;
    /** @var int     一页显示几条数据 */
    protected $_config_rows = 20;
    /** @var array   第一页 */
    protected $_config_index = [];
    /** @var string  获取数据总数 */
    protected $_config_count_sql = 'count(*)';

    /** @var string */
    protected $_where_str = '';
    /** @var array */
    protected $_where_bind = [];

    /** @var \ounun\pdo */
    protected $_db;
    /** @var string */
    protected $_table;
    /** @var string */
    protected $_url;

    /** @var int 数量总量 */
    protected $_total;
    /** @var int 页数总量(除去首页) */
    protected $_total_page = 0;
    /** @var int 页数总量(总数) */
    protected $_total_page_real = 1;

    /** @var int 当前所在页数 */
    protected $_page_curr = 1;
    /** @var bool 翻页排序  false:1...max  true:max...1 */
    protected $_page_end_index = false;

    /**
     * 创建一个分页类
     * page constructor.
     * @param \ounun\pdo $db
     * @param string $table
     * @param string $url
     * @param array $where
     * @param array $config
     */
    public function __construct(\ounun\pdo $db, string $table, string $url, array $where = [], array $config = [])
    {
        $this->_db = $db;
        $this->_table = $table;
        $this->_url = $url;

        if($where && is_array($where)){
            foreach (['str','bind'] as $key){
                if($where[$key]){
                    $m = "_where_{$key}";
                    $this->$m = $where[$key];
                }
            }
        }

        $this->config_set($config);
    }

    /**
     * 设定总接口
     * @param array $config
     */
    public function config_set(array $config)
    {
        // print_r($config);
        if($config && is_array($config)){
            foreach (['note','page_tag_default','page_tag_curr','page_tag_name','show_max','rows','index','count_sql'] as $key){
                if($config[$key]){
                    $m = "_config_{$key}";
                    $this->$m = $config[$key];
                }
            }
        }
    }

    /**
     * 得到分页数据
     * @param int $page
     * @param string $title
     * @param bool $end_index
     * @return array
     */
    public function init(int $page = 0, string $title = "", bool $end_index = false): array
    {
        $tag_default = $this->_config_page_tag_default;
        $tag_curr    = $this->_config_page_tag_curr;
        $tag_name    = $this->_config_page_tag_name;

        $title = $title ? "{$title}-" : '';
        $pages = [];

        $data = $this->_data($page, $end_index);
        $note = $this->_note_set();

        $url_prev = '';
        $url_next = '';
        foreach ($data as $v) {
            if ($v['begin']) {
                $pages[] = $tag_default[0] . '<a href="' . $this->_url_set($v['begin']) . '" title="' . $title . '第' . $v['begin'] . '页" '.$tag_default[2].'>' . htmlspecialchars($tag_name[0]) . '</a>' . $tag_default[1];
            } elseif ($v['previous']) {
                $url_prev = $this->_url_set($v['previous']);
                $pages[] = $tag_default[0] . '<a href="' . $url_prev . '" title="' . $title . '第' . $v['previous'] . '页" '.$tag_default[2].'>' . htmlspecialchars($tag_name[1]) . '</a>' . $tag_default[1];
            } elseif ($v['next']) {
                $url_next = $this->_url_set($v['next']);
                $pages[] = $tag_default[0] . '<a href="' . $url_next . '" title="' . $title . '第' . $v['next'] . '页" '.$tag_default[2].'>' . htmlspecialchars($tag_name[2]) . '</a>' . $tag_default[1];
            } elseif ($v['end']) {
                $pages[] = $tag_default[0] . '<a href="' . $this->_url_set($v['end']) . '" title="' . $title . '第' . $v['end'] . '页" '.$tag_default[2].'>' . htmlspecialchars($tag_name[3]) . '</a>' . $tag_default[1];
            } elseif ($v['default']) {
                if ($this->_page_curr == $v['default']) {
                    $pages[] = $tag_curr[0] . '<a href="' . $this->_url_set($v['default']) . '" title="' . $title . '第' . $v['default'] . '页" '.$tag_curr[2].' onclick="return false">' . $v['default'] . '</a>' . $tag_curr[1];
                } else {
                    $pages[] = $tag_default[0] . '<a href="' . $this->_url_set($v['default']) . '" title="' . $title . '第' . $v['default'] . '页" '.$tag_default[2].'>' . $v['default'] . '</a>' . $tag_default[1];
                }
            } elseif ($v['index']) {
                $pages[] = $tag_default[0] . '<a href="' . $this->_url_set(0) . '" title="' . $title .$tag_name[4]. '" '.$tag_default[2].'>' . htmlspecialchars($tag_name[4]) . '</a>' . $tag_default[1];
            }
        }
        return [
            'url_prev' => $url_prev,
            'url_next' => $url_next,

            'page_total' => $this->_total_page,
            'page_curr'  => $this->_page_curr,

            'note' => $note,
            'page' => $pages
        ];
    }

    /**
     * 算出分页数据
     * @param int $page_curr
     * @param bool $end_index
     * @return array
     */
    protected function _data(int $page_curr = 0, bool $end_index = false): array
    {
        $page_middle       = ceil($this->_config_show_max / 2);

        $this->_total           = $this->total();
        $this->_total_page_real = ceil($this->_total / $this->_config_rows);
        if($end_index){
            $this->_total_page  = $this->_total_page_real - 1;
        }else{
            $this->_total_page  = $this->_total_page_real;
        }

        $page_curr = $end_index ? ($page_curr < 1 ? 0 : $page_curr) : ($page_curr < 1 ? 1 : $page_curr);
        $page_curr = $page_curr > $this->_total_page ? $this->_total_page : $page_curr;

        $this->_page_curr      = $page_curr;
        $this->_page_end_index = $end_index;

        if ($this->_total_page > $this->_config_show_max) {
            $sub_total = $this->_config_show_max;
            $sub_begin = true;
            $sub_end   = true;
            if ($page_curr <= $page_middle) {
                $sub_begin = false;
                $sub_start = 1;
            } elseif ($this->_total_page - $page_curr < $page_middle) {
                $sub_end   = false;
                $sub_start = $this->_total_page - $this->_config_show_max + 1;
            } else {
                $sub_start = $page_curr - $page_middle + 1;
            }
        } else {
            $sub_total = $this->_total_page;
            $sub_begin = false;
            $sub_end   = false;
            $sub_start = 1;
        }
        $sub_index = $page_curr > 0 ? true : false;
        $sub_next = ($page_curr < $this->_total_page && $this->_total_page > 1) ? true : false;
        $sub_previous = ($page_curr > 1 && $this->_total_page > 1) ? true : false;

        // 载入np数据
        $rs = [];
        $sub_index && $rs[] = ['index' => 100000000];
        $sub_begin && $rs[] = ['begin' => 1];
        $sub_previous && $rs[] = ['previous' => $page_curr - 1];
        for ($i = $sub_start; $i < $sub_start + $sub_total; $i++) {
            $rs[] = ['default' => $i];
        }
        $sub_next && $rs[] = ['next' => $page_curr + 1];
        $sub_end && $rs[] = ['end'  => $this->_total_page];
        return $rs;
    }

    /**
     * 得到数据总行数
     * @return int
     */
    public function total(): int
    {
        if ($this->_total) {
            return $this->_total;
        }
        $this->_total = $this->_total_get();
        return $this->_total;
    }

    /**
     * 得到数据总页数
     * @return int
     */
    public function total_page(): int
    {
        return $this->_total_page;
    }

    /**
     * 当前所在页数
     * @return int
     */
    public function page_curr(): int
    {
        return $this->_page_curr;
    }

    /**
     * 翻页排序  false:1...max  true:max...1
     * @return int
     */
    public function page_end_index(): int
    {
        return $this->_page_end_index;
    }

    /**
     * @return int
     */
    public function limit_rows(): int
    {
        return $this->_config_rows;
    }

    /**
     * @return int
     */
    public function limit_start(): int
    {
        if ($this->_page_end_index && $this->_page_curr == 0 ) {
            $start = $this->_total - $this->_config_rows;
        } else {
            $start = ($this->_page_curr - 1) * $this->_config_rows;
        }
        return $start < 0 ? 0 : $start;
    }

    /**
     * 设定字符串
     * @return string
     */
    private function _note_set(): string
    {
        $replace = [$this->_total, $this->_total_page_real, $this->_page_curr];
        return str_replace(['{total}', '{total_page}', '{page}'], $replace, $this->_config_note);
    }

    /**
     * 设定URL串
     * @param int $page
     * @return string
     */
    protected function _url_set(int $page): string
    {
        $search  = [];
        $replace = [];
        $url     = str_replace('{page}', $page, $this->_url);
        if ($this->_config_index) {
            if ($this->_page_end_index ) {
                if( $page == 0 ){
                    foreach ($this->_config_index as $v){
                        $search[]  = str_replace('{total_page}', $this->_total_page, $v[0]);
                        $replace[] = $v[1];
                    }
                }
            } else {
                if (1 == $page){
                    foreach ($this->_config_index as $v){
                        $search[]  = $v[0];
                        $replace[] = $v[1];
                    }
                }
            }
        }
        if($search){
            $url = str_replace($search, $replace, $url);
        }
        return $url;
    }

    /**
     * 从数据库中得到数据总行数
     * @return int
     */
    protected function _total_get(): int
    {
        $rs = $this->_db->table($this->_table)
            ->field(' ' . $this->_config_count_sql . ' as `cc` ')
            ->where($this->_where_str, $this->_where_bind)->column_one();

        if ($rs && $rs['cc']) {
            return (int)$rs['cc'];
        }
        return 0;
    }
}
