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

class base
{
    /** @var string  提示串 */
    protected $_config_note = '总共有{total}条数据,共{total_page}页,第{page}页';
    /** @var array   默认页 */
    protected $_config_default = ['<li>', '</li>'];
    /** @var array   当前页面时 */
    protected $_config_now = ['<li class="now">', '</li>'];
    /** @var array   第一页 上一页 下一页 最后一页   ['|&lt;','&lt;','&gt;','&gt;|']; */
    protected $_cfg_tag = ['第一页', '上一页', '下一页', '最后一页'];
    /** @var int     最多显示几页 */
    protected $_cfg_max = 7;
    /** @var int     一页显示几条数据 */
    protected $_cfg_rows = 20;
    /** @var array   第一页 */
    protected $_cfg_index = [];

    /** @var \ounun\pdo */
    protected $_db;
    /** @var string */
    protected $_table;
    /** @var string */
    protected $_url;
    /** @var string */
    protected $_where_str = '';
    /** @var array */
    protected $_where_bind = [];
    /** @var string */
    protected $_sql_count;


    /** @var int 数量总量 */
    protected $_total;
    /** @var int 页数总量 */
    protected $_total_page = 1;
    /** @var int 当前所在页数 */
    protected $_page = 1;
    /** @var bool 翻页排序  false:1...max  true:max...1 */
    protected $_page_max = false;

    /**
     * 创建一个分页类
     * page constructor.
     * @param \ounun\pdo $db
     * @param string $table
     * @param string $url
     * @param string $where_str
     * @param array $where_bind
     * @param string $sql_count
     * @param array $config
     */
    public function __construct(\ounun\pdo $db, string $table, string $url, string $where_str = '', array $where_bind = [], string $sql_count = 'count(*)', array $config = [], int $rows = 0)
    {
        $this->_db = $db;
        $this->_table = $table;
        $this->_url = $url;
        $this->_where_str = $where_str;
        $this->_where_bind = $where_bind;
        $this->_sql_count = $sql_count;

        if ($config) {
            $this->config_set($config);
        }

        if ($rows) {
            $this->_cfg_rows = $rows;
        }
    }

    /**
     * 设定总接口
     * @param string|array $key
     * @param string $value
     */
    public function config_set(array $config)
    {
        // 提示串
        if ($config['note']) {
            $this->_config_note = $config['note'];
        }
        // 默认页
        if ($config['default']) {
            $this->_config_default = $config['default'];
        }
        // 当前页面时
        if ($config['now']) {
            $this->_config_now = $config['now'];
        }
        // 第一页 上一页 下一页 最后一页
        if ($config['tag']) {
            $this->_cfg_tag = $config['tag'];
        }
        // 最多显示几页
        if ($config['max']) {
            $this->_cfg_max = $config['max'];
        }
        // 一页显示几条数据
        if ($config['rows']) {
            $this->_cfg_rows = $config['rows'];
        }
        // 第一页
        if ($config['index']) {
            $this->_cfg_index = $config['index'];
        }
    }

    /**
     * 得到分页数据
     * @param int $page
     * @param array $config
     * @return array
     */
    public function init(int $page = 0, string $title = "", bool $default_end = false): array
    {
        $page_default = $this->_config_default;
        $page_now = $this->_config_now;
        $cfg_tag = $this->_cfg_tag;
        $title = $title ? "{$title}-" : '';

        $rs_page = [];

        $data = $this->data($page, $default_end);
        $rs_note = $this->_note_set($this->_total, $this->_total_page, $this->_page);

        $url_prev = '';
        $url_next = '';
        foreach ($data as $v) {
            if ($v['begin']) {
                $rs_page[] = $page_default[0] . '<a href="' . $this->_url_set($v['begin']) . '" title="' . $title . '第' . $v['begin'] . '页">' . $cfg_tag[0] . '</a>' . $page_default[1];
            } elseif ($v['previous']) {
                $url_prev = $this->_url_set($v['previous']);
                $rs_page[] = $page_default[0] . '<a href="' . $url_prev . '" title="' . $title . '第' . $v['previous'] . '页">' . $cfg_tag[1] . '</a>' . $page_default[1];
            } elseif ($v['next']) {
                $url_next = $this->_url_set($v['next']);
                $rs_page[] = $page_default[0] . '<a href="' . $url_next . '" title="' . $title . '第' . $v['next'] . '页">' . $cfg_tag[2] . '</a>' . $page_default[1];
            } elseif ($v['end']) {
                $rs_page[] = $page_default[0] . '<a href="' . $this->_url_set($v['end']) . '" title="' . $title . '第' . $v['end'] . '页">' . $cfg_tag[3] . '</a>' . $page_default[1];
            } elseif ($v['def']) {
                if ($this->_page == $v['def']) {
                    $rs_page[] = $page_now[0] . '<a href="' . $this->_url_set($v['def']) . '" title="' . $title . '第' . $v['def'] . '页" ' . $page_now[2] . ' onclick="return false">' . $v['def'] . '</a>' . $page_now[1];
                } else {
                    $rs_page[] = $page_default[0] . '<a href="' . $this->_url_set($v['def']) . '" title="' . $title . '第' . $v['def'] . '页">' . $v['def'] . '</a>' . $page_now[1];
                }
            }
        }
        return [
            'url_prev' => $url_prev,
            'url_next' => $url_next,
            'page_total' => $this->_total_page,
            'page_now' => $this->_page,
            'note' => $rs_note,
            'page' => $rs_page
        ];
    }

    /**
     * 算出分页数据
     * @param int $page
     * @param bool $default_end
     * @return array
     */
    public function data(int $page = 0, bool $default_end = false): array
    {
        $m = ceil($this->_cfg_max / 2);
        $this->_total = $this->total();
        $this->_total_page = ceil($this->_total / $this->_cfg_rows);
        $page = $default_end
            ? ($page < 1 ? $this->_total_page : $page)
            : ($page < 1 ? 1 : $page);
        $this->_page = $page;
        $this->_page_max = $default_end;

        if ($this->_total_page > $this->_cfg_max) {
            $sub_total = $this->_cfg_max;
            $sub_begin = true;
            $sub_end = true;
            if ($page <= $m) {
                $sub_start = 1;
                $sub_begin = false;
            } elseif ($this->_total_page - $page < $m) {
                $sub_start = $this->_total_page - $this->_cfg_max + 1;
                $sub_end = false;
            } else {
                $sub_start = $page - $m + 1;
            }
        } else {
            $sub_total = $this->_total_page; //
            $sub_begin = false;
            $sub_end = false;
            $sub_start = 1;
        }
        $sub_next = ($page != $this->_total_page && $this->_total_page > 1) ? true : false;
        $sub_previous = ($page != 1 && $this->_total_page > 1) ? true : false;
        // 载入np数据
        $rs = [];
        $sub_begin && $rs[] = ['begin' => 1];
        $sub_previous && $rs[] = ['previous' => $page - 1];
        for ($i = $sub_start; $i < $sub_start + $sub_total; $i++) {
            $rs[] = ['def' => $i];
        }
        $sub_next && $rs[] = ['next' => $page + 1];
        $sub_end && $rs[] = ['end' => $this->_total_page];
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
    public function page(): int
    {
        return $this->_page;
    }

    /**
     * 翻页排序  false:1...max  true:max...1
     * @return int
     */
    public function page_max(): int
    {
        return $this->_page_max;
    }

    /**
     * @return int
     */
    public function limit_rows(): int
    {
        return $this->_cfg_rows;
    }

    /**
     * @return int
     */
    public function limit_start(): int
    {
        if ($this->_page_max && $this->_page == $this->_total_page) {
            $start = $this->_total - $this->_cfg_rows;
        } else {
            $start = ($this->_page - 1) * $this->_cfg_rows;
        }
        return $start < 0 ? 0 : $start;
    }

    /**
     * 设定字符串
     * @param array $arr
     * @return string
     */
    private function _note_set(int $total, int $total_page, int $page): string
    {
        return str_replace(['{total}', '{total_page}', '{page}'], [$total, $total_page, $page], $this->_config_note);
    }

    /**
     * 设定URL串
     * @param int $page
     * @return string
     */
    protected function _url_set(int $page): string
    {
        $url = str_replace('{page}', $page, $this->_url);
        if ($this->_cfg_index) {
            if ($this->_page_max && $page == $this->_total_page) {
                if (is_array($this->_cfg_index)) {
                    $cfg_index = str_replace('{total_page}', $page, $this->_cfg_index[0]);
                    $url = str_replace($cfg_index, $this->_cfg_index[1], $url);
                } else {
                    $cfg_index = str_replace('{total_page}', $page, $this->_cfg_index);
                    $url = str_replace($cfg_index, '', $url);
                }
            } elseif (1 == $page) {
                if (is_array($this->_cfg_index)) {
                    $url = str_replace($this->_cfg_index[0], $this->_cfg_index[1], $url);
                } else {
                    $url = str_replace($this->_cfg_index, '', $url);
                }
            }
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
            ->field(' ' . $this->_sql_count . ' as `cc` ')
            ->where($this->_where_str, $this->_where_bind)->column_one();
        //  ->row("select {$this->_sql_count} as `cc` from {$this->_table} {$this->_where_str}", $this->_where_bind);
        if ($rs && $rs['cc']) {
            return (int)$rs['cc'];
        }
        return 0;
    }
}
