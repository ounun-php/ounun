<?php

namespace plugins\curl;


class parse
{
    /**
     * 获取目录内容(左边)
     * @param string $content 所在内容
     * @param string $left 目标内容左边标识点
     * @param string $right 目标内容右边标识点
     * @return mixed
     */
    static public function left(string $content, string $left, string $right)
    {
        return explode($right, explode($left, $content, 2)[1], 2)[0];
    }

    /**
     * 获取目录内容(右边)
     * @param string $content  所在内容
     * @param string $right    目标内容右边标识点
     * @param string $left     目标内容左边标识点
     * @return mixed
     */
    static public function right(string $content, string $right, string $left)
    {
        return explode($left, explode($right, $content, 2)[0], 2)[1];
    }

    /**
     * 获取目录内容(左右两边边)
     * @param string $content  所在内容
     * @param string $left     目标内容左边标识点
     * @param string $right    目标内容右边标识点
     * @return string
     */
    static public function left_right(string $content, string $left, string $right)
    {
        $pos = strpos($content, $left);
        if ($pos !== false) {
            $content = substr($content, $pos + strlen($left));
        }
        $pos = strrpos($content, $right);
        if ($pos === false) {
            return $content;
        }
        return substr($content, 0, $pos);
    }

    /**
     * 获取目录内容(左边)
     * @param string $content  所在内容
     * @param string $middle   目标内容分格点
     * @param string $left     目标内容左边标识点
     * @param string $right    目标内容右边标识点
     * @return array
     */
    static public function list_left(string $content, string $middle, string $left, string $right)
    {
        $rs = [];
        $c2 = explode($middle, $content);
        foreach ($c2 as $v2) {
            $rs[] = self::left($v2, $left, $right);
        }
        return $rs;
    }

    /**
     * 获取目录内容(右边)
     * @param string $content  所在内容
     * @param string $middle   目标内容分格点
     * @param string $right    目标内容右边标识点
     * @param string $left     目标内容左边标识点
     * @return array
     */
    static public function list_right(string $content, string $middle, string $right, string $left)
    {
        $rs = [];
        $c2 = explode($middle, $content);
        foreach ($c2 as $v2) {
            $rs[] = self::right($v2, $right, $left);
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
    static public function preg_match_all(string $pattern, string $subject)
    {
        $matches = [];
        preg_match_all('/' . $pattern . '/', $subject, $matches, PREG_SET_ORDER);
        return $matches;
    }
}
