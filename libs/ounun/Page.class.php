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
class Page
{
    private $total;
    private $static_total;
    private $page 	        = 1;
    private $page_cnt       = 1;
    private $rows;
    private $max 	    	= 7;

    private $rs_str   	    = '总共有{total}条数据,共{pageCnt}页,第{page}页';
    private $rs_default 	= array('<li>', '</li>');
    private $rs_now 	  	= array('<li class="now">', '</li>');
    private $rs_tag 	  	= array('|&lt;','&lt;','&gt;','&gt;|');
    private $rs_one_page 	= null;
    //
    private $db;
    private $table;
    private $where;
    private $url;
    private $returns;
    /**
     * 创建一个分页类
     *
     * @param resource $db
     * @param string $table
     * @param string $url
     * @param string $where
     * @param array $config
     */
    public function __construct($db, $table, $url, $where = null, $config = null,$rows=20)
    {
        if(is_numeric($db))
        {
            $this->static_total = $db;
            $table && $this->set($table);
        }
        else
        {
            $config && $this->set($config);
            $this->db    = $db;
            $this->table = $table;
            $this->where = $where;
        }
        $this->rows = $rows;
        $this->url  = $url;
    }
    /**
     * 得到分页数据
     *
     * @param int $page
     * @param array $config
     * @return array
     */
    public function init($page = null,$count = 'count(*)', $config = array())
    {
        $this->returns || $this->data($page,$count, $config);
        $returns        = $this->returns;
        $returns['str'] = $this->_set_str(array($returns['total'], $returns['pageCnt'], $returns['this']));
        $default = $this->rs_default;
        $tag     = $this->rs_tag;
        $now     = $this->rs_now;
        foreach ($returns['np'] as &$v)
        {
            if($v['Begin'])
            {
                $v = $default[0] . '<a href="' . $this->_set_url($v['Begin']) . '" title="转到第一页">'.$tag[0].'</a>' . $default[1];
            }
            elseif($v['Previous'])
            {
                $v = $default[0] . '<a href="' . $this->_set_url($v['Previous']) . '" title="上一頁">'.$tag[1].'</a>' . $default[1];
            }
            elseif($v['Next'])
            {
                $v = $default[0] . '<a href="' . $this->_set_url($v['Next']) . '" title="下一页">'.$tag[2].'</a>' . $default[1];
            }
            elseif($v['End'])
            {
                $v = $default[0] . '<a href="' . $this->_set_url($v['End']) . '" title="转到最后一页">'.$tag[3].'</a>' . $default[1];
            }
            else
            {
                $tmp = $v;
                $v   = '';
                foreach ($tmp as $value)
                {
                    if($returns['this'] == $value)
                    {
                        $v .= $now[0] . '<a href="' . $this->_set_url($value) . '" title="本页" '.$now[2].' onclick="return false">' . $value . '</a>' . $now[1];
                    }
                    else
                    {
                        $v = $default[0] . '<a href="' . $this->_set_url($value) . '">' . $value . '</a>' . $now[1];
                    }
                }
            }
        }
        return $returns;
    }
    /**
     * 算出分页数据
     *
     * @param int $page
     * @param array $config
     * @return array
     */
    public function data($page = null,$count = 'count(*)', $config = array())
    {
        $config && $this->set($config);
        $sub      = $returns = array();
        $sub['t'] = $this->max;
        $sub['m'] = ceil($this->max / 2);
        $page     = (int)$page?$page:$this->page;
        $returns['this']    = $page    = $this->page     = $page < 1?1:$page;
        $returns['total']   = $total   = $this->getTotal($count);
        $returns['pageCnt'] = $pageCnt = $this->page_cnt = ceil($total / $this->rows);
        $returns['begin']   = $this->rows * ($page - 1);
        $returns['rows']    = $this->rows;
        if($pageCnt > $sub['t'])
        {
            $sub['C'] = $sub['t'];
            $sub['Begin'] = true;
            $sub['End'] = true;
            if($returns['this'] <= $sub['m'])
            {
                $sub['Start'] = 1;
                $sub['Begin'] = false;
            }
            elseif($pageCnt - $page < $sub['m'])
            {
                $sub['Start'] = $pageCnt - $sub['t'] + 1;
                $sub['End'] = false;
            }
            else
            {
                $sub['Start'] = $returns['this'] - $sub['m'] + 1;
            }
        }
        else
        {
            $sub['C']     = $pageCnt; //
            $sub['Begin'] = false;
            $sub['End']   = false;
            $sub['Start'] = 1;
        }
        $sub['Next']     = ($page != $pageCnt && $pageCnt > 1)?true:false;
        $sub['Previous'] = ($page != 1        && $pageCnt > 1)?true:false;
        //载入np数据
        $returns['np'] = array();
        $sub['Begin']    && $returns['np'][] = array('Begin'=>1);
        $sub['Previous'] && $returns['np'][] = array('Previous'=>$page - 1);
        for($i = $sub['Start']; $i < $sub['Start'] + $sub['C']; $i++)
        {
            $returns['np'][] = array($i);
        }
        $sub['Next'] && $returns['np'][]  = array('Next'=>$page + 1);
        $sub['End']  && $returns['np'][]  = array('End'=>$pageCnt);
        unset($sub);
        return $this->returns = $returns;
    }
    /**
     * 设定总接口
     *
     * @param string|array $key
     * @param string $value
     */
    public function set($key, $value=null)
    {
        if(is_array($key))
        {
            foreach ($key as $k=>$v)
            {
                !is_numeric($k) && $v && $this->$k = $v;
            }
        }
        else
        {
            !is_numeric($key) && $value && $this->$key = $value;
        }
    }
    /**
     * 设死数据总行数
     *
     * @param int $pageCnt
     */
    public function setStaticTotal($pageCnt)
    {
        $this->static_total = $pageCnt;
    }
    /**
     * 设定 字符串 选中或没选中前后的HTML代码
     *
     * @param string $str
     * @param array $default
     * @param array $now
     */
    public function setHtml($str = null, $default = null, $now = null)
    {
        $str && $this->rs_str = $str;
        is_array($default) && $this->rs_default = $default;
        is_array($now)     && $this->rs_now     = $now;
    }
    /**
     * 得到数据总行数
     *
     * @return int
     */
    public function getTotal($count)
    {
        if($this->total)
        {
            return $this->total;
        }elseif($this->static_total)
        {
            return $this->static_total;
        }
        $this->total = (int)$this->_get_total($count);
        return $this->total;
    }
    /**
     * 得到数据总页数
     *
     * @return int
     */
    public function getPageCnt()
    {
        return $this->page_cnt;
    }
    /**
     * 设定字符串
     *
     * @param array $arr
     * @return string
     */
    private function _set_str($arr)
    {
        return str_replace(array('{total}', '{pageCnt}', '{page}'), $arr, $this->rs_str);
    }
    /**
     * 设定URL串
     *
     * @param int $page
     * @return string
     */
    private function _set_url($page)
    {
        $url     = str_replace('{page}', $page, $this->url);
        if($this->rs_one_page)
        {
            $url = str_replace($this->rs_one_page, '', $url);
        }
        return $url;
    }
    /**
     * 从数据库中得到数据总行数
     *
     * @return int
     */
    private function _get_total($count)
    {
        $rs = $this->db->row("select $count as cc from {$this->table} {$this->where['where']}", $this->where['bind']);
        if($rs && $rs['cc'])
        {
            return (int)$rs['cc'];
        }
        return 0;
    }
}
?>