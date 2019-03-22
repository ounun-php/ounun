<?php
/**
 * 返回cms_www
 * @return \extend\cms\adm
 */
function cmsa()
{
    return v::$cms;
}

/**
 * @return \ounun\mvc\model\admin\oauth
 */
function oauth()
{
    return \ounun\mvc\model\admin\oauth::instance();
}
