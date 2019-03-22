<?php
/** 命名空间 */
namespace app\adm\controller;

/**
 * 数据统计
 * @author dreamxyp
 */
class site extends adm
{
    /**
     * 站群列表
     * @param array $mod
     */
    public function site_list($mod)
    {
        // 权限
        $this->_nav_pur_check('site/site_list.html','site@site_list', '站点列表','站点',\adm_purv::nav_null);

        // $db_libs  = self::db('libs');
        $table   = ' `adm_site_info` ';
        if ($_GET['act'] == 'del')
        {
            $this->_db_v->delete($table,'`site_tag`= :site_tag ',$_GET);
            // 跳回原来的页面
             go_back();
        }

        $where       = [];
        if($_GET['zqun_tag'])
        {
            $where[] = ' `zqun_tag`= :zqun_tag ';
        }
        if($_GET['cdn'])
        {
            $where[] = ' `cdn`= :cdn ';
        }
        if($_GET['host'])
        {
            $where[] = ' `host`= :host ';
        }
        if($_GET['beian'])
        {
            $_GET['beian2'] = '-' == $_GET['beian']?'':$_GET['beian'];
            $where[] = ' `beian`= :beian2 ';
        }
        if($_GET['q'])
        {
            $_GET['q2'] = "%{$_GET['q']}%";
            $where[] = ' ( `main_domain` like :q2 or `name` like :q2  ) ';
        }
        $where       = $where?' where '.implode(' and ', $where):'';
        /** 分页 */
        $page        = (int)$_GET['page'];
        $page        = $page>1?$page:$page;
        $rows        = 30;
        $where_bind  = $_GET;

        $url     = \ounun\page_util::url();
        $pg      = new \ounun\page\base($this->_db_v,$table,$url,$where,$where_bind,'count(*)',\c::Page_Config_B,$rows);
        $ps      = $pg->init($page,"");

        $data	 = $this->_db_v->data_array("select * from {$table} {$where} ORDER BY `state` DESC, `type` DESC, `db` ASC, `zqun_tag` ASC , `site_tag` ASC limit {$pg->limit_start()},{$rows}", $where_bind);
        // echo $db_libs->sql();
        require $this->require_file('_site/site_list.html.php');
    }

    /**
     * 添加站群
     * @param array $mod
     */
    public function site_bash($mod)
    {
        // 权限
        $this->_nav_pur_check('site/site_bash.html','site@site_list', '站点Bash','站点',\adm_purv::nav_null);

        // $db_libs  = $this->_db_v;
        $table    = ' `adm_site_info` ';

        $where    = [];
        if($_GET['host'])
        {
            $where[] = ' `host`= :host ';
        }
        $where       = $where?' where '.implode(' and ', $where):'';
        $where_bind  = $_GET;


        $data	     = $this->_db_v->data_array("select `state`,`zqun_tag`,`site_tag` from {$table} {$where} ORDER BY `type` DESC, `zqun_tag` ASC , `site_tag` ASC ;", $where_bind);
        $zqun_cc     = [];
        if($data)
        {
            // echo $db_libs->sql()."<br />\n";
            $zqun_cc   = [];
            foreach ($data as $v){
                $zqun_cc[$v['zqun_tag']] = $v['zqun_tag'];
            }
            $zqun_cc  = array_values($zqun_cc);
            $zqun_cc  = $this->_db_v->fetch_assoc('SELECT `zqun_tag`, `dir` FROM `adm_zqun` where `zqun_tag` in(?) group by `zqun_tag` ;',$zqun_cc,'zqun_tag');
        }
        // print_r(['$zqun_cc'=>$zqun_cc,'$data'=>$data]);
        require $this->require_file('_site/site_bash.html.php');
    }

    /**
     * 添加站群
     * @param array $mod
     */
    public function site_add($mod)
    {
        // 权限
        $this->_nav_pur_check('site/site_add.html','site@site_list', '添加/编辑(站点)','站点',\adm_purv::nav_null);

        // $db_libs    = self::db('libs');
        $table      = ' `adm_site_info` ';
        $scfg_cache = \scfg_cache::instance($this->_db_v);
        $zqun       = $scfg_cache->zqun();

        if ($_POST && $_POST['site_tag'])
        {
            //exit();
            $this->_db_v->active();
            \scfg_cache::instance($this->_db_v)->site_clean();
            $this->_site_add_post($_POST,$table,$this->_db_v,$zqun);
            exit();
        }

        $host   = $scfg_cache->host();
        $rs     = [];
        if($_GET['site_tag'])
        {
            $rs = $this->_db_v->row("SELECT * FROM {$table} where `site_tag` = :site_tag ;",$_GET);
        }
        // print_r(['$zqun'=>$zqun]);
        require $this->require_file('_site/site_add.html.php');
    }



    protected function _site_add_post(array $post,string $table,\ounun\mysqli $db_libs,array $zqun)
    {
        $zqun_data = null;
        foreach ($zqun as $v)
        {
            if($zqun_data == null && $post['zqun_tag'] == $v['zqun_tag'] )
            {
                $zqun_data = $v;
            }
        }

        $dns    = \uitls\comm::str2array($post['dns'],'tag,sub_domain,cdn,host');
        $stat   = \uitls\comm::str2array($post['stat'],'tag,stat_uid');
        $bind   = [
            'site_tag'       => $post['site_tag'],
            'zqun_tag'       => $post['zqun_tag'],
            'type'           => $zqun_data['type'],
            'main_domain'    => $post['main_domain'],
            'name'           => $post['name'],
            'site_cls'       => $post['site_cls'],
            'cdn'            => $post['cdn'],
            'api'            => $post['api'],
            'dns'            => json_encode($dns,JSON_UNESCAPED_UNICODE),
            'host'           => $post['host'],
            'beian'          => $post['beian'],
            'state'          => $post['state'],
            'stat'           => json_encode($stat,JSON_UNESCAPED_UNICODE),
            'db'             => is_array($post['db'])  ?json_encode($post['db']  ,JSON_UNESCAPED_UNICODE):$post['db'],
            'exts'           => is_array($post['exts'])?json_encode($post['exts'],JSON_UNESCAPED_UNICODE):$post['exts'],
        ];

        $rs = $db_libs->row("SELECT `site_tag` FROM {$table} where `site_tag` = :site_tag ;",$post);

        // print_r(['$bind'=>$bind]);
        // echo $db_libs->sql()."<br  />\n";
        if($rs && $rs['site_tag'])
        {
            $db_libs->update($table,$bind," `site_tag` = :site_tag ",$post);
            // exit($db_libs->sql());
            \ounun::go_back();
        }else
        {
            $bind['site_tag'] = $post['site_tag'];
            $db_libs->insert($table,$bind);
            // exit($db_libs->sql());
            \ounun::go_url(\ounun\page_util::page('site_list.html'));
        }
    }

    /**
     * 站群列表
     * @param array $mod
     */
    public function zqun_list($mod)
    {
        // 权限
        $this->_nav_pur_check('site/zqun_list.html','site@zqun_list', '站群列表','站群',\adm_purv::nav_null);

        // $db_libs  = self::db('libs');
        $table   = ' `adm_zqun` ';
        if ($_GET['act'] == 'del')
        {
            $this->_db_v->delete($table,'`zqun_tag`= :zqun_tag ',$_GET);
            // 跳回原来的页面
            \ounun::go_back();
        }

        $where      = [];
        $where      = $where?' where '.implode(' and ', $where):'';
        /** 分页 */
        $page       = (int)$_GET['page'];
        $page       =      $page>1?$page:$page;
        $rows       = 50;
        $where_bind = $_GET;


        $url     = \ounun\page_util::url();
        $pg      = new \ounun\page($this->_db_v,$table,$url,$where,$where_bind,'count(*)',\status::page_cfg,$rows);
        $ps      = $pg->init($page,"");

        $data	 = $this->_db_v->data_array("select * from {$table} {$where} ORDER BY `type` DESC, `zqun_tag` ASC limit {$pg->limit_start()},{$rows}", $where_bind);

        $site_cc   = [];
        foreach ($data as $v){
            $site_cc[$v['zqun_tag']] = $v['zqun_tag'];
        }
        $site_cc  = array_values($site_cc);
        $site_cc  = $this->_db_v->fetch_assoc('SELECT `zqun_tag`,count(`site_tag`) as `cc` FROM `adm_site_info` where `zqun_tag` in(?) group by `zqun_tag` ;',$site_cc,'zqun_tag');


        require $this->require_file('_site/zqun_list.html.php');
    }

    /**
     * 添加站群
     * @param array $mod
     */
    public function zqun_add($mod)
    {
        // 权限
        $this->_nav_pur_check('site/zqun_add.html','site@zqun_list', '添加/编辑站群','站群',\adm_purv::nav_null);

        // $db_libs = self::db('libs');
        $table   = ' `adm_zqun` ';

        if ($_POST && $_POST['zqun_tag'])
        {
            //exit();
            $this->_db_v->active();
            \scfg_cache::instance($this->_db_v)->zqun_clean();
            $this->_zqun_add_post($_POST,$table);
            exit();
        }
        $rs     = [];
        if($_GET['zqun_tag'])
        {
            $rs = $this->_db_v->row("SELECT * FROM {$table} where `zqun_tag` = :zqun_tag ;",$_GET);
        }
        require $this->require_file('_site/zqun_add.html.php');
    }

    protected function _zqun_add_post(array $post,string $table)
    {
        $bind         = [
            'zqun_tag'       => $post['zqun_tag'],
            'type'           => $post['type'],
            'name'           => $post['name'] ,
            'dir'            => $post['dir'],
            'svn'            => $post['svn'],
            'exts'           => is_array($post['exts'])?json_encode($post['exts'],JSON_UNESCAPED_UNICODE):$post['exts'],
        ];


        $rs = $this->_db_v->row("SELECT `zqun_tag` FROM {$table} where `zqun_tag` = :zqun_tag ;",$post);
        // echo $db_libs->sql()."<br  />\n";
        if($rs && $rs['zqun_tag'])
        {
            $this->_db_v->update($table,$bind," `zqun_tag` = :zqun_tag ",$post);
            // exit($db_libs->sql());
            \ounun::go_back();
        }else
        {
            $bind['zqun_tag'] = $post['zqun_tag'];
            $this->_db_v->insert($table,$bind);
            // exit($db_libs->sql());
            \ounun::go_url(\ounun\page_util::page('zqun_list.html'));
        }
    }

    /**
     * 服务器列表
     * @param array $mod
     */
    public function host_list($mod)
    {
        // 权限
        $this->_nav_pur_check('site/host_list.html','site@host_list', '服务器列表','服务器',\adm_purv::nav_null);

        // $db_libs  = self::db('libs');
        $table   = ' `adm_host` ';
        if ($_GET['act'] == 'del')
        {
            $this->_db_v->delete($table,'`host_tag`= :host_tag ',$_GET);
            // 跳回原来的页面
            \ounun::go_back();
        }

        $where      = [];
        $where      = $where?' where '.implode(' and ', $where):'';
        /** 分页 */
        $page       = (int)$_GET['page'];
        $page       =      $page>1?$page:$page;
        $rows       = 50;
        $where_bind = $_GET;


        $url     = \ounun\page_util::url();

        $pg      = new \ounun\page($this->_db_v,$table,$url,$where,$where_bind,'count(*)',\status::page_cfg,$rows);
        $ps      = $pg->init($page,"");

        $data	 = $this->_db_v->data_array("select * from {$table} {$where} ORDER BY `host_type` DESC, `host_tag` ASC limit {$pg->limit_start()},{$rows}", $where_bind);

        $site_cc   = [];
        foreach ($data as $v){
            $site_cc[$v['host_tag']] = $v['host_tag'];
        }
        $site_cc  = array_values($site_cc);
        $site_cc  = $this->_db_v->fetch_assoc('SELECT `host`,count(`site_tag`) as `cc` FROM `adm_site_info` where `host` in(?) group by `host` ;',$site_cc,'host');

        // print_r($hosts);

        require $this->require_file('_site/host_list.html.php');
    }

    /**
     * 添加服务器
     * @param array $mod
     */
    public function host_add($mod)
    {
        // 权限
        $this->_nav_pur_check('site/host_add.html','site@host_list', '添加/编辑服务器','服务器',\adm_purv::nav_null);

        // $db_libs = self::db('libs');
        $table   = ' `adm_host` ';

        if ($_POST && $_POST['host_tag'])
        {
            // exit();
            $this->_db_v->active();
            \scfg_cache::instance($this->_db_v)->host_clean();
            $this->_host_add_post($_POST,$table);
            exit();
        }
        $rs     = [];
        if($_GET['host_tag'])
        {
            $rs = $this->_db_v->row("SELECT * FROM {$table} where `host_tag` = :host_tag ;",$_GET);
        }
        require $this->require_file('_site/host_add.html.php');
    }

    protected function _host_add_post(array $post,string $table)
    {
        $bind         = [
            'host_tag'       => $post['host_tag'],
            'host_type'      => $post['host_type'],
            'room'           => $post['room'] ,
            'name'           => $post['name'],
            'private_ip'     => $post['private_ip'],
            'public_ip'      => $post['public_ip'],
            'exts'           => is_array($post['exts'])?json_encode($post['exts'],JSON_UNESCAPED_UNICODE):$post['exts'],
        ];

        $rs = $this->_db_v->row("SELECT `host_tag` FROM {$table} where `host_tag` = :host_tag ;",$post);

        // print_r(['$bind'=>$bind]);
        // echo $db_libs->sql()."<br  />\n";
        if($rs && $rs['host_tag'])
        {
            $this->_db_v->update($table,$bind," `host_tag` = :host_tag ",$post);
            // exit($db_libs->sql());
            \ounun::go_back();
        }else
        {
            $bind['host_tag'] = $post['host_tag'];
            $this->_db_v->insert($table,$bind);
            // exit($db_libs->sql());
            \ounun::go_url(\ounun\page_util::page('host_list.html'));
        }
    }



//    public function link_adm($mod)
//    {
//        // 权限
//        // $this->_site_type_only = [\adm_purv::app_type_admin];
//        $this->_nav_pur_check('site/link_adm.html','site@link_adm', '友情连接[adm]','友情',\adm_purv::nav_null);
//        $db_www = self::db('libs');
//        require $this->require_file('_site/link_list.html.php');
//    }

    /**
     * 友情连接
     * @param array $mod
     */
    public function link($mod)
    {
        // 权限
        $this->_site_type_only = [\adm_purv::app_type_site];
        $this->_nav_pur_check('site/link.html','site@link', '友情连接','友情',\adm_purv::nav_site);

        // $db_www = self::db('libs');
        $table  = '`z_site_links`';
        if($_GET && $_GET['id'] && 'del' == $_GET['act'])
        {
            $this->_db_site->delete($table,' `id` = :id  ',$_GET);
            \ounun::go_url($this->_page_url);
        }elseif ($_POST)
        {
            $this->_db_site->active();
            $this->_link_post($_POST,$table);
            exit();
        }

        $datas  = $this->_db_site->data_array("SELECT * FROM {$table} ORDER by type_id ASC,sort DESC");
        $rs     = [];
        if($_GET['id'])
        {
            $rs = $this->_db_site->row("SELECT * FROM {$table} where  `id` = :id ;",$_GET);
        }
        // ---------------------------------------
        require $this->require_file('_site/link_list.html.php');
    }

    protected function _link_post(array $post,string $table)
    {
        $post['id']   =  (int)$post['id'];
        $bind         = [
            'type_id'     => $post['type_id'],
            'sort'        => $post['sort'],
            'url'         => $post['url'],
            'site_name'   => $post['site_name'],
            'time_add'    => strtotime($post['time_add']." 00:00:00"),
            'time_end'    => strtotime($post['time_end']." 00:00:00"),
            'is_nofollow' => $post['is_nofollow'],
            'is_check'    => $post['is_check'],
        ];
        if($post['id']){
            $this->_db_site->update($table,$bind," `id` = :id ",$post);
        }else{
            $this->_db_site->insert($table,$bind);
        }
        \ounun::go_back();
    }


    /**
     * 站点配制
     * @param array $mod
     */
    public function config_list($mod)
    {
        // 权限
        $this->_site_type_only = [\adm_purv::app_type_site];
        $this->_nav_pur_check('site/config_list.html','site@config_list', '站点配制','配制',\adm_purv::nav_site);

        // $db_libs  = self::db('libs');
        $table   = ' `z_site_config` ';
        if ($_GET['act'] == 'del')
        {
            $this->_db_site->delete($table,'`id`= :id ',$_GET);
            // 跳回原来的页面
            \ounun::go_back();
        }

        $where      = [];
        $where      = $where?' where '.implode(' and ', $where):'';
        /** 分页 */
        $page       = (int)$_GET['page'];
        $page       =      $page>1?$page:$page;
        $rows       = 50;
        $where_bind = $_GET;


        $url     = \ounun\page_util::url();
        $pg      = new \ounun\page($this->_db_site,$table,$url,$where,$where_bind,'count(*)',\status::page_cfg,$rows);
        $ps      = $pg->init($page,"");

        $data	 = $this->_db_site->data_array("select * from {$table} {$where} ORDER BY `id` ASC limit {$pg->limit_start()},{$rows}", $where_bind);
        // print_r($data);

        require $this->require_file('_site/config_list.html.php');
    }


    /**
     * 添加/编辑(站点配制)
     * @param array $mod
     */
    public function config_add($mod)
    {
        // 权限
        $this->_site_type_only = [\adm_purv::app_type_site];
        $this->_nav_pur_check('site/config_add.html','site@config_list', '添加/编辑(站点配制)','配制',\adm_purv::nav_site);

        // $db_libs = self::db('libs');
        $table   = ' `z_site_config` ';

        if ($_POST && $_POST['id'])
        {
            $this->_db_site->active();
            $this->_config_add_post($_POST,$table);
            exit();
        }
        $rs     = [];
        if($_GET['id'])
        {
            $rs = $this->_db_site->row("SELECT * FROM {$table} where `id` = :id ;",$_GET);
        }

        require $this->require_file('_site/config_add.html.php');
    }

    protected function _config_add_post(array $post,string $table)
    {
        $bind         = [
            'id'            => $post['id'],
            'mod_id'        => $post['mod_id'],
            'key'           => $post['key'] ,
            'name'          => $post['name'],
            'value'         => is_array($post['value'])?json_encode($post['value'],JSON_UNESCAPED_UNICODE):$post['value'],
        ];

        if($post['id'])
        {
            $this->_db_site->update($table,$bind," `id` = :id ",$post);
            // exit($db_libs->sql());
            \ounun::go_back();
        }else
        {
            $this->_db_site->insert($table,$bind);
            // exit($db_libs->sql());
            \ounun::go_url(\ounun\page_util::page('config_list.html'));
        }
    }



    /**
     * 站点地图
     * @param array $mod
     */
    public function sitemap_list($mod)
    {
        // 权限
        $this->_site_type_only = [\adm_purv::app_type_site];
        $this->_nav_pur_check('site/sitemap_list.html','site@sitemap_list', '站点地图','站点地图',\adm_purv::nav_site);

        // $db_libs  = self::db('libs');
        $table   = ' `z_sitemap` ';
        if ($_GET['act'] == 'del')
        {
            $this->_db_site->delete($table,'`url_id`= :url_id ',$_GET);
            // 跳回原来的页面
            \ounun::go_back();
        }

        $where      = [];
        if($_GET['mod'])
        {
            $where[] = ' `mod`= :mod ';
        }
        $where      = $where?' where '.implode(' and ', $where):'';
        /** 分页 */
        $page       = (int)$_GET['page'];
        $page       =      $page>1?$page:$page;
        $rows       = 50;
        $where_bind = $_GET;


        $url     = \ounun\page_util::url();
        $pg      = new \ounun\page($this->_db_site,$table,$url,$where,$where_bind,'count(*)',\status::page_cfg,$rows);
        $ps      = $pg->init($page,"");

        $data	 = $this->_db_site->data_array("select * from {$table} {$where} ORDER BY `lastmod` DESC ,`url_id` ASC limit {$pg->limit_start()},{$rows}", $where_bind);
        // print_r($data);
        // echo $this->_db_site->sql();

        require $this->require_file('_site/sitemap_list.html.php');
    }



    /**
     * 站点push统计
     * @param array $mod
     */
    public function sitemap_stat($mod)
    {
        // 权限
        $this->_site_type_only = [\adm_purv::app_type_site];
        $this->_nav_pur_check('site/sitemap_stat.html','site@sitemap_stat', '站点push统计','站点地图',\adm_purv::nav_site);

        $data	= $this->_db_site->data_array("SELECT COUNT(`id`) as `cc`, `target_id`,`Ymd` FROM `z_sitemap_push` GROUP BY `target_id`,`Ymd` ORDER BY `Ymd` DESC,`target_id` LIMIT 50;");
        // print_r($data);

        require $this->require_file('_site/sitemap_stat.html.php');
    }



    /**
     * 站点地图[统计]
     * @param array $mod
     */
    public function sitemap_stat_map($mod)
    {
        // 权限
        $this->_site_type_only = [\adm_purv::app_type_site];
        $this->_nav_pur_check('site/sitemap_stat_map.html','site@sitemap_stat_map', '站点地图[统计]','站点地图',\adm_purv::nav_site);

        $data	= $this->_db_site->data_array("SELECT `mod`, COUNT(`url_id`) as cc FROM `z_sitemap` GROUP by `mod`;");
        // print_r($data);
        require $this->require_file('_site/sitemap_stat_map.html.php');
    }

}