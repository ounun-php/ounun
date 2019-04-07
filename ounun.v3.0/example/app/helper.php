<?php

/**
 * 静态地址
 * @param array|string $url
 * @param string $pre_str
 * @return string
 */
function surl($url, string $pre_str = ""): string
{
    return \ounun\config::url_static($url, \ounun\config::$url_static . $pre_str);
}

/**
 * 静态地址(G)
 * @param string|array $url
 * @param string $pre_str
 * @return string
 */
function gurl($url, string $pre_str = ""): string
{
    return \ounun\config::url_static($url, \ounun\config::$url_static_g . $pre_str);
}


/**
 * 返回cms_www
 * @return \extend\cms\www
 */
function cms()
{
    return v::$cms;
}

/**
 * 返回 i18n
 * @return ounun\mvc\model\i18n
 */
function i18n()
{
    return \ounun\config::i18n_get();
}

/**
 * @return array Cmd
 */
function cmds()
{
    echo "\\ounun\\cmd\\def\\help::class:" . \ounun\cmd\def\help::class . "\n";
    return [

    ];
}
