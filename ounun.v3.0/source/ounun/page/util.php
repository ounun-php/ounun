<?php

namespace ounun\page;

class util
{
    /**
     * @param array $paras
     * @param array $page_paras
     * @param string $url_original
     * @return string
     */
    static public function url(array $paras = [], array $page_paras = [], string $url_original = '')
    {
        $paras = $paras ? $paras : $_GET;
        $page_paras = $page_paras ? $page_paras : ['page' => '{page}'];
        $url_original = $url_original ? $url_original : url_original();
        $url = url_build_query($url_original, $paras, $page_paras);
        self::page_set($_SERVER['REQUEST_URI']);
        return $url;
    }

    /**
     * 设定当前页面
     * @param string $url
     * @param string $url_key
     */
    static public function page_set(string $url, string $url_key = 'p')
    {
        self::val_set($url_key, $url);
    }

    /**
     * 获取URL
     * @param string $default_url
     * @param string $url_key
     * @return mixed
     */
    static public function page(string $default_url, string $url_key = 'p')
    {
        return self::val($url_key, $default_url);
    }

    /**
     * 设定当前页
     * @param int $page 页数
     */
    static public function cur_set(int $page = 1, string $page_key = 'page')
    {
        self::val_set($page_key, $page);
    }

    /**
     * 获取当前页
     * @param string $pre
     * @param string $page_key GET 页数key
     * @param int $default_page 默认忽略 的页数
     * @return string
     */
    static public function cur(string $pre = '?', string $page_key = 'page', int $default_page = 1)
    {
        $page = self::val($page_key, $default_page);
        if ($page == $default_page) {
            return '';
        }
        return "{$pre}{$page_key}={$page}";
    }

    /**
     * 设定值
     * @param string $key
     * @param mixed $value
     */
    static public function val_set(string $key, $value)
    {
        setcookie("pu_{$key}", $value, time() * 1.2, '/');
    }

    /**
     * 获取值
     * @param string $key 值key
     * @param mixed $default_value 如值为空，返回本值
     * @return mixed
     */
    static public function val(string $key, $default_value)
    {
        $val = $_COOKIE["pu_{$key}"];
        return $val ? $val : $default_value;
    }
}
