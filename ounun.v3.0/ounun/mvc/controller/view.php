<?php
namespace ounun\mvc\controller;
/**
 * Class VodBase
 */

class view extends \ounun\view
{
    /** @var \cms\cms_pics */
    public static $cms;
    /** @var \seo\base */
    public static $seo;

    /** @var \ounun\mysqli DB */
    protected $_db_v  = null;

    public static function db(string $key, $db_cfg = null): \ounun\mysqli
    {
        $key = IsDebug?"{$key}_debug":$key;

        // echo "\$key:{$key}\n";
        return parent::db($key, $db_cfg);
    }

    /** 初始化 */
    public function init(string $url = '',bool $is_cache = true,bool $is_replace = true)
    {
        self::$seo       = new \seo\base($url);
        self::$cms       = new \cms\cms_pics(self::$seo);

        //      $dir_tpl_root    = '';
        //      $dir_tpl_root_g  = '';
        $dir_tpl_root    = '';
        $dir_tpl_root_g  = '';
        if(null == $this->_db_v)
        {
            $this->_db_v = self::db(scfg::$app);
        }
        self::$cms->db   = $this->_db_v;
        $this->init_complete($is_cache,$is_replace,$dir_tpl_root,$dir_tpl_root_g);
    }

    /**
     * @param bool $is_cache
     * @param bool $is_replace
     * @param string $dir_tpl_root
     */
    public function init_complete(bool $is_cache = true,bool $is_replace = true,string $dir_tpl_root = "",string $dir_tpl_root_g = "")
    {
        $this->_global_replace();
        $this->template(\scfg::$tpl,\scfg::$tpl_default,$dir_tpl_root,$dir_tpl_root_g);
        if(IsDebug)
        {
            if($is_replace)
            {
                self::$_stpl->replace(self::$seo,false);
            }
        }else
        {
            if($is_cache)
            {
                if(self::$_html_cache)
                {
                    self::$_html_cache->replace(self::$seo);
                }
            }elseif($is_replace)
            {
                self::$_stpl->replace(self::$seo,$this->_html_trim);
            }
        }
    }

    /** Cache */
    public function html_cache($key)
    {
        if(!IsDebug)
        {
            $cfg                = $GLOBALS['_scfg']['cache_file'];
            $cfg['mod']         = \scfg::$app.\scfg::$tpl;
            self::$_html_cache  = new \ounun\html(\scfg::$app,\scfg::$tpl,$cfg,$key,$this->_html_cache_time,$this->_html_trim,false);

            self::$_html_cache->run(true);
        }
    }

    /** 赋值(默认) */
    protected function _global_replace()
    {
        $url_base            = substr($this->_page_url,1);
        self::$seo->sets([
            '{$url_www}'          => scfg::$url_www,
            '{$url_wap}'          => scfg::$url_mobile,
            '{$url_mip}'          => scfg::$url_mip,
            '{$url_api}'          => scfg::$url_api,
            '{$url_app}'          => scfg::url_page(),

            '{$page_url}'         => $this->_page_url ,
            '{$page_file}'        => $this->_page_file,

            '{$canonical_pc}'     => scfg::$url_www.$url_base,
            '{$canonical_mip}'    => scfg::$url_mip.$url_base,
            '{$canonical_wap}'    => scfg::$url_mobile.$url_base,

            '{$app}'              => scfg::$app,
            '{$domain}'           => scfg::$app_domain,

            '{$sres}'             => scfg::$url_res,
            '{$static}'           => scfg::$url_static,
            '{$static_g}'         => scfg::$url_static_g,
            '"/static/'           => '"'.scfg::$url_static,
        ]);
    }
}