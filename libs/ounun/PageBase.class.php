<?php 
namespace ounun;
/**
 * 主要功能: 分頁,有問題問我吧,沒時間寫注
 *
 * dreamxyp(QQ:31996798) - Page.class.php
 * coding:夏一平
 * 創建時間:2006-10-30
 * @example
 *
 */
class PageBase
{
    protected $_cfg_note   	= '总共有{total}条数据,共{total_page}页,第{page}页'; // 提示串
    protected $_cfg_default = ['<li>', '</li>'];              // 默认页
    protected $_cfg_now 	= ['<li class="now">', '</li>'];  // 当前页面时
    protected $_cfg_tag 	= ['|&lt;','&lt;','&gt;','&gt;|'];// 第一页 上一页 下一页 最后一页
    protected $_cfg_max 	= 7;  // 最多显示几页
    protected $_cfg_rows    = 20; // 一页显示几条数据
    protected $_cfg_index 	= []; // 第一页

    //
    protected $_db;
    protected $_table;
    protected $_url;
    protected $_where_str;
    protected $_where_bind;
    protected $_sql_count;


    protected $_total;
    protected $_total_page    = 1;
    protected $_page 	      = 1;
    
    /**
     * 创建一个分页类
     *
     * @param resource $db
     * @param string $table
     * @param string $url
     * @param string $where
     * @param array $config
     */
    public function __construct($db, $table, $url, $where_str = '', $where_bind = [], $sql_count = 'count(*)',  $config    = [])
    {
        $this->_db    = $db;
        $this->_table = $table;
        $this->_url   = $url;
        $this->_where_str  = $where_str;
        $this->_where_bind = $where_bind;
        $this->_sql_count  = $sql_count;

        if($config)
        {
            $this->set_config($config['rows'],$config['max'],
                              $config['index_page'],$config['note'],
                              $config['tag'],$config['now'],$config['default']);
        }
    }

    /**
     * 设定总接口
     * @param string|array $key
     * @param string $value
     */
    public function set_config($rows,$max,$index_page,$note,$tag,$now,$default)
    {
        if($note)
        {
            $this->_cfg_note   	= $note;        // 提示串
        }
        if($default)
        {
            $this->_cfg_default = $default;     // 默认页
        }
        if($now)
        {
            $this->_cfg_now 	= $now;         // 当前页面时
        }
        if($tag)
        {
            $this->_cfg_tag 	= $tag;         // 第一页 上一页 下一页 最后一页
        }
        if($max)
        {
            $this->_cfg_max 	= $max;         // 最多显示几页
        }
        if($rows)
        {
            $this->_cfg_rows    = $rows;        // 一页显示几条数据
        }
        if($index_page)
        {
            $this->_cfg_index 	= $index_page;  // 第一页
        }
    }
    /**
     * 得到分页数据
     *
     * @param int $page
     * @param array $config
     * @return array
     */
    public function init($page=0,$title="",$default_end = false)
    {
        $page_default    = $this->_cfg_default;
        $page_now        = $this->_cfg_now;
        $cfg_tag         = $this->_cfg_tag;
        $title           = $title?"{$title}-":'';

        $rs_page         = [];

        $data            = $this->data($page,$default_end);
        $rs_note         = $this->_set_note($this->_total, $this->_total_page, $this->_page);
        foreach ($data as $v)
        {
            if($v['begin'])
            {
                $rs_page[] = $page_default[0] . '<a href="' . $this->_set_url($v['begin']) . '" title="'.$title.'第'.$v['begin'].'页">'.$cfg_tag[0].'</a>' . $page_default[1];
            }
            elseif($v['previous'])
            {
                $rs_page[] = $page_default[0] . '<a href="' . $this->_set_url($v['previous']) . '" title="'.$title.'第'.$v['previous'].'页">'.$cfg_tag[1].'</a>' . $page_default[1];
            }
            elseif($v['next'])
            {
                $rs_page[] = $page_default[0] . '<a href="' . $this->_set_url($v['next']) . '" title="'.$title.'第'.$v['next'].'页">'.$cfg_tag[2].'</a>' . $page_default[1];
            }
            elseif($v['end'])
            {
                $rs_page[] = $page_default[0] . '<a href="' . $this->_set_url($v['end']) . '" title="'.$title.'第'.$v['end'].'页">'.$cfg_tag[3].'</a>' . $page_default[1];
            }
            elseif ($v['def'])
            {
                if($this->_page == $v['def'])
                {
                    $rs_page[] = $page_now[0] . '<a href="' . $this->_set_url($v['def']) . '" title="'.$title.'第'.$v['def'].'页" '.$page_now[2].' onclick="return false">' . $v['def'] . '</a>' . $page_now[1];
                }
                else
                {
                    $rs_page[] = $page_default[0] . '<a href="' . $this->_set_url($v['def']) . '" title="'.$title.'第'.$v['def'].'页">' . $v['def'] . '</a>' . $page_now[1];
                }
            }
        }
        return ['note'=>$rs_note,'page'=>$rs_page];
    }
    
    /**
     * 算出分页数据
     *
     * @param int $page
     * @param array $config
     * @return array
     */
    public function data($page = 0,$default_end=false)
    {
        $m                 = ceil($this->_cfg_max / 2);
        $this->_total      = $this->total();
        $this->_total_page = ceil($this->_total / $this->_cfg_rows);
        $page              = $default_end
                                ?($page < 1?$this->_total_page:$page)
                                :($page < 1 ? 1 : $page);
        $this->_page       = $page;

        if($this->_total_page > $this->_cfg_max)
        {
            $sub_total    = $this->_cfg_max;
            $sub_begin    = true;
            $sub_end      = true;
            if($page <= $m)
            {
                $sub_start    = 1;
                $sub_begin    = false;
            }
            elseif($this->_total_page - $page < $m)
            {
                $sub_start    = $this->_total_page - $this->_cfg_max + 1;
                $sub_end      = false;
            }
            else
            {
                $sub_start    = $page - $m + 1;
            }
        }
        else
        {
            $sub_total    = $this->_total_page; //
            $sub_begin    = false;
            $sub_end      = false;
            $sub_start    = 1;
        }
        $sub_next         = ($page != $this->_total_page && $this->_total_page > 1)?true:false;
        $sub_previous     = ($page != 1                  && $this->_total_page > 1)?true:false;
        // 载入np数据
        $rs               = [];
        $sub_begin       && $rs[]  = ['begin'=>1];
        $sub_previous    && $rs[]  = ['previous'=>$page - 1];
        for($i = $sub_start; $i < $sub_start + $sub_total; $i++)
        {
            $rs[]                  = ['def' =>$i];
        }
        $sub_next        && $rs[]  = ['next'=>$page + 1];
        $sub_end         && $rs[]  = ['end' =>$this->_total_page];
        return $rs;
    }

    
    /**
     * 得到数据总行数
     * @return int
     */
    public function total():int
    {
        if($this->_total)
        {
            return $this->_total;
        }
        $this->_total = $this->_get_total();
        return $this->_total;
    }
    
    /**
     * 得到数据总页数
     *
     * @return int
     */
    public function total_page()
    {
        return $this->_total_page;
    }

    /**
     * @return int
     */
    public function limit_rows()
    {
        return $this->_cfg_rows;
    }

    /**
     * @return int
     */
    public function limit_start()
    {
        $start = ($this->_page-1)*$this->_cfg_rows;
        return $start<0?0:$start;
    }

    /**
     * 设定字符串
     *
     * @param array $arr
     * @return string
     */
    private function _set_note($total, $total_page, $page)
    {
        return str_replace(['{total}','{total_page}', '{page}'], [$total,$total_page,$page], $this->_cfg_note);
    }
    
    /**
     * 设定URL串
     *
     * @param int $page
     * @return string
     */
    protected function _set_url($page)
    {
        $url     = str_replace('{page}', $page, $this->_url);
        if($this->_cfg_index)
        {
            $url = str_replace($this->_cfg_index, '', $url);
        }
        return $url;
    }
    
    /**
     * 从数据库中得到数据总行数
     *
     * @return int
     */
    protected function _get_total():int
    {
        $rs = $this->_db->row("select {$this->_sql_count} as cc from {$this->_table} {$this->_where_str}", $this->_where_bind);
        if($rs && $rs['cc'])
        {
            return (int)$rs['cc'];
        }
        return 0;
    }
}
