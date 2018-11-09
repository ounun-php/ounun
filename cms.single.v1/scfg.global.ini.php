<?php
/** 是否开发环境 **/
define('IsDebug',                   file_exists('/Users/dreamxyp/Transcend/') );
/** data目录 **/
define('Dir_Data_ProJ',             Dir_Root.'proj.data/');
/** cache目录 **/
define('Dir_Cache',          	    Dir_Root.'proj.cache/');
/** libs目录 **/
// define('Dir_TemplateG',             dirname(Dir_Libs_Cms).'/cms.single.template.v1/');

/** ***********************************************************************
 * cache_file
 ** ********************************************************************** */
$GLOBALS['_scfg']['cache_file']  = [
    'type' 			=> \ounun\cache::Type_File,
    'mod'  			=> 'html',
    'root' 			=> Dir_Cache,
    'format_string' => false,
    'large_scale' 	=> true,
];


class scfg extends ounun_scfg
{
    /** 技支的语言 */
    public static $langs  = [
        "en"=>"English",
     // "zh"=>"繁體中文",
        "cn"=>"简体中文",
     // "ja"=>"日本語",
    ];

    /**
     * 静态地址
     * @param array|string $url
     * @param string $pre_str
     * @return string
     */
    public static function surl($url, string $pre_str = ""): string
    {
        return parent::surl($url, scfg::$url_static.$pre_str);
    }

    /**
     * 静态地址(G)
     * @param $url
     * @param string $pre_str
     * @return string
     */
    public static function gurl($url, string $pre_str = ""): string
    {
        return parent::surl($url, scfg::$url_static_g.$pre_str);
    }

    /**
     * 配制文件
     * @param string $host
     * @param array $mod
     */
    public function __construct(array $mod,string $host,string $lang_default = 'cn',string $lang = 'cn',array $dirs = [],array $libs = [],array $routes = [])
    {
        if($mod && $mod[0] && self::$langs[$mod[0]])
        {
            $lang = array_shift($mod);
        }
        parent::__construct($mod,$host,$lang_default,$lang,$dirs,$libs,$routes);
    }
}


/**
 * Class VodBase
 */
class v extends \ounun_view
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
            $this->_db_v = self::db(\ounun_scfg::$app);
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


/** 开始 */
function start($req,$host,$argv,string $lang_default,string $lang,string $dir_root,string $dir_root_app,string $lib_ounun,string $lib_cms,string $lib_app,array $routes=[],array $routes_default=[],array $base_urls=[])
{
    // 解析URL
    if($argv && $argv[1])
    {
        // error_reporting(E_ALL ^ E_NOTICE);
        $mod    = $argv[1];
        $mod    = explode(',', $mod);
        $host   = $argv[2]?$argv[2]:'adm';
        if('zrun_' != substr($mod[0],0,5) )
        {
            exit("error php shell only:zrun_*\n");
        }
    }else
    {
        $uri 	= \ounun::url_original($req);
        $mod	= \ounun::url_to_mod($uri);
    }
    /** 初始化scfg */
    $dirs = [
        'root'     => $dir_root,
        'root_app' => $dir_root_app,
    ];
    $libs = [
        'ounun'    => $lib_ounun,
        'cms'      => $lib_cms,
        'app'      => $lib_app,
    ];
    $route = [
        'routes'          => $routes,
        'routes_default'  => $routes_default,
    ];
    $scfg = new scfg($mod,$host,$lang_default,$lang,$dirs,$libs,$route);
    if($base_urls)
    {
        $scfg->urls($base_urls['url_www'],$base_urls['url_wap'],$base_urls['url_mip'],$base_urls['url_api'],$base_urls['url_res'],$base_urls['url_static'],$base_urls['url_static_g'],$base_urls['app_domain']);
    }
    /** 开始 */
    new ounun($scfg);
}
