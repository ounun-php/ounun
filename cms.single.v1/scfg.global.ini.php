<?php
/** 是否开发环境 **/
define('IsDebug',                   file_exists('/Users/dreamxyp/Transcend/') );
/** data目录 **/
define('Dir_Data_ProJ',             Dir_Root.'proj.data/');
/** cache目录 **/
define('Dir_Cache',          	    Dir_Root.'proj.cache/');
/** 项目代号    */
define('Const_Static_Idx',  	 	12);
/** 内部服务器与中心服务器通信密码 */
define('Const_Key_Conn_Private',  	'512009757a6e7f57b76dcd5edf378e67');

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
     * @param string|array $url
     * @param string       $pre_str
     * @param bool         $static_root
     * @return string
     */
    public static function surl($url, string $pre_str = "", string $static_root = Const_Url_Static): string
    {
        return parent::surl($url, "{$static_root}{$pre_str}");
    }

    /**
     * 静态地址(G)
     * @param $url
     * @param string $pre_str
     * @param string $static_root
     * @return string
     */
    public static function gurl($url, string $pre_str = "", string $static_root = Const_Url_StaticG): string
    {
        return parent::surl($url, "{$static_root}{$pre_str}");
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
    /** @var \cms\seo_comm */
    public static $seo;

    /** @var \ounun\mysqli DB */
    protected $_db_v;

    public static function db(string $key, $db_cfg = null): \ounun\mysqli
    {
        $key = IsDebug?"{$key}_debug":$key;

        // echo "\$key:{$key}\n";
        return parent::db($key, $db_cfg);
    }

    /** 初始化 */
    public function init(string $url = '',bool $is_cache = true,bool $is_replace = true, string $dir_tpl_root ="")
    {
        $this->_db_v    = self::db(\ounun_scfg::$app);

        self::$seo      = new \cms\seo_comm($url);
        self::$cms      = new \cms\cms_pics(self::$seo);
        self::$cms->db  = $this->_db_v;

        $this->template(\scfg::$tpl,\scfg::$tpl_default,$dir_tpl_root);
        if(IsDebug)
        {
            if($is_replace)
            {
                $this->_global_replace();
                self::$_stpl->replace($this->_replace_data,false);
            }
        }else
        {
            if($is_cache)
            {
                $this->_global_replace();
                if(self::$_html_cache)
                {
                    self::$_html_cache->replace($this->_replace_data);
                }
            }elseif($is_replace)
            {
                $this->_global_replace();
                self::$_stpl->replace($this->_replace_data,$this->_html_trim);
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
        $static              = scfg::surl('');
        $static_g            = scfg::gurl('');
        $url_base            = substr($this->_page_url,1);
        $this->replace_sets([
            '{$url_www}'         => Const_Url_Www,
            '{$url_api}'         => Const_Url_Api,
            '{$url_app}'         => scfg::url_page(),
            '{$page_url}'        => $this->_page_url,
            '{$canonical_pc}'    => Const_Url_Www.$url_base,
            '{$canonical_mip}'   => Const_Url_Mip.$url_base,
            '{$page_file}'       => $this->_page_file,
            '{$domain}'          => Const_Domain,
            '{$app}'             => \scfg::$app,
            '{$static}'          => $static,
            '{$static_g}'        => $static_g,
            //'{$idx}'           => Const_Static_Idx,
            '"/static/'          => '"'.$static,
        ]);
        $this->replace_sets(self::$seo->tkd());
    }
}


/** 开始 */
function start($req,$host,$argv,string $lang_default,string $lang,string $dir_root,string $dir_root_app,string $lib_ounun,string $lib_cms,string $lib_app,array $routes=[],array $routes_default=[])
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
        $uri 	= \ounun::url_original($_SERVER['REQUEST_URI']);
        $mod	= \ounun::url_to_mod($uri);
        $host   = $_SERVER["HTTP_HOST"];
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
    //  $scfg->dirs($dir_root , $dir_root_app);
    //  $scfg->libs($lib_ounun, $lib_cms, $lib_app);
    /** 开始 */
    new ounun($scfg);
}
