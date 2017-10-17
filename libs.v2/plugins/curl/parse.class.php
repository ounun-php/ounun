<?php
namespace plugins\curl;


class parse
{
    /**
     * 获取目录内容(左边)
     * @param $c string 所在内容
     * @param $l string 目标内容左边标识点
     * @param $r string 目标内容右边标识点
     */
    static public function left($c, $l, $r)
    {
        return explode($r,explode($l,$c,2)[1],2)[0];
    }

    /**
     * 获取目录内容(右边)
     * @param $c string 所在内容
     * @param $r string 目标内容右边标识点
     * @param $l string 目标内容左边标识点
     */
    static public function right($c, $r, $l)
    {
        return explode($l,explode($r,$c,2)[0],2)[1];
    }

    /**
     * 获取目录内容(左右两边边)
     * @param $c string 所在内容
     * @param $l string 目标内容左边标识点
     * @param $r string 目标内容右边标识点
     */
    static public function left_right($c, $l, $r)
    {
        $pos = strpos($c,$l);
        if($pos !== false)
        {
            $c   = substr($c,$pos+strlen($l));
        }
        $pos = strrpos($c,$r);
        if($pos === false)
        {
            return $c;
        }
        return substr($c,0,$pos);
    }

    /**
     * 获取目录内容(左边)
     * @param $c string 所在内容
     * @param $m string 目标内容分格点
     * @param $l string 目标内容左边标识点
     * @param $r string 目标内容右边标识点
     */
    static public function list_left($c, $m, $l, $r)
    {
        $rs = [];
        $c2 = explode($m,$c);
        foreach ($c2 as $v2)
        {
            $rs[] = self::left($v2,$l,$r);
        }
        return $rs;
    }

    /**
     * 获取目录内容(右边)
     * @param $c string 所在内容
     * @param $m string 目标内容分格点
     * @param $r string 目标内容右边标识点
     * @param $l string 目标内容左边标识点
     */
    static public function list_right($c, $m, $r, $l)
    {
        $rs = [];
        $c2 = explode($m,$c);
        foreach ($c2 as $v2)
        {
            $rs[] = self::right($v2,$r,$l);
        }
        return $rs;
    }

    /**
     * 取出正则数据
     * @param  $pattern string
     *      网址: <a href="(http://:any)">(:any)</a>
     *      网址: <img src="(http://:any)" :any?/>
     * @param  $subject string
     * @return mixed
     */
    static public function preg_match_all(string $pattern,string $subject)
    {
        $matches  = [];
        preg_match_all('/'.$pattern.'/', $subject, $matches, PREG_SET_ORDER);
        return $matches;
    }
}