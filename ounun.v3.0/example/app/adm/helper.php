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

function site_zqun()
{
    // $scfg_cache = \extend\cache_config::instance($this->_db_v);
    $site0 = [];// $scfg_cache->site();
    $site = [];
    foreach ($site0 as $k2 => $v2) {
        foreach ($v2 as $k => $v) {
            if ($v['state']) {
                $site[$v['zqun_tag']][] = [
                    'k' => $v['site_tag'],
                    'name' => $v['name'],
                    'type' => $v['type'],
                    'domain' => $v['main_domain'],
                ];
            }
        }
    }

    $zqun0 = []; // $scfg_cache ->zqun();
    $zqun = [];
    foreach ($zqun0 as $v) {
        if ($site[$v['zqun_tag']]) {
            $zqun[$v['zqun_tag']] = $v['name'];
        }
    }
    return ['site' => $site, 'zqun' => $zqun];
}
