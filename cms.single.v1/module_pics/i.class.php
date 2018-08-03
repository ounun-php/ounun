<?php
namespace module_pics;

class i extends \v
{
    const table_sitemap         = ' `sitemap` ';

    const table_sitemap_push    = ' `sitemap_push` ';

    const type_baidu_mip        = 1;
    const type_baidu_map        = 2;

    /** 全部提交频率 45天 */
    const push_rate             = 3888000;  // 3600 * 24 * 45

    /** 每次提交数量 */
    const push_step             = 1000;

    /** 接口最大提交量 每天 */
    const push_max    = [
        self::type_baidu_mip  =>    9500, // 10000
        self::type_baidu_map  => 5000000,
    ];



    /**
     * i constructor.
     * @param $mod
     */
    public function __construct($mod)
    {
        $this->_db_v = self::db('core');
        parent::__construct($mod);
    }


    /**
     * @param $data
     * @param $is_replace  0:采集  1:更新  2:新插入
     */
    protected function _db_data_insert($data,$data_exts,$is_replace=0)
    {
//        print_r([
//            '$data'=>$data,'$data_exts'=>$data_exts,'$is_replace'=>$is_replace
//        ]);
        $this->_db_v->active();
        $table   = '`data`';
        $data_id = (int)$data['data_id'];
        $mod_id  = (int)$data['mod_id'];
        $mods    = array_keys(\site_cfg::mods);
        if(!$mod_id){
            echo "mod_id 为0 --> \$mod_id:{$mod_id}\n";
            return;
        }
        if(!in_array($mod_id,$mods)){
            echo "mod_id 不为[".implode(',',$mods)."] --> \$mod_id:{$mod_id}\n";
            return;
        }
        if($data_id){
            if(0 == $is_replace)
            {
                $rs = $this->_db_data_check($data_id);
                if($rs)
                {
                    echo "采集 数据：--> \$data_id:{$data_id}  已存在 \n";
                }else{
                    $data['exts']    = json_encode($data['exts']);
                    $data['data_id'] = $data_id;
                    $this->_db_v->insert($table,$data);
                    $sql = $this->_db_v->sql();
                    $rs  = $this->_db_data_check($data_id);
                    if($rs)
                    {
                        echo "采集 数据 插入成功 --> \$data_id:{$data_id} 《{$data['title']}》\n";
                    }else{
                        echo "  error sql:{$sql}\n";
                        echo "采集 插入失败 --> \$data_id:{$data_id} 《{$data['title']}》\n";
                    }
                }
                if($data_exts)
                {
                    $this->_db_data_insert2($mod_id,$data_id,$data_exts);
                }
            }elseif (1 == $is_replace){  // 更新

            }elseif (2 == $is_replace){  // 新插入

            }
        }else{
            if(1 == $is_replace){  // 更新

            }elseif (2 == $is_replace){  // 新插入

            }
        }
    }

    /**
     * @param $data_id
     * @param $data_exts
     * @param $is_replace  0:采集  1:更新  2:新插入
     */
    protected function _db_data_insert2($mod_id,$data_id,$data_exts){
        if(!$data_id){
            echo "\$data_id 为0 --> \$data_id:{$data_id}\n";
            return;
        }
        if(2 == $mod_id){
            $table = 'data_star';
            $field = 'star_id';
        }elseif (3 == $mod_id){
            $table = 'data_news';
            $field = 'news_id';
        }elseif (4 == $mod_id){
            $table = 'data_pics';
            $field = 'pics_id';
        }elseif (11 == $mod_id){
            $table = 'data_special';
            $field = 'special_id';
        }else
        {
            echo "出错 --> \$mod_id:{$mod_id} \$data_id:{$data_id}\n";
            return;
        }
        // --------------------------------------
        $rs    = $this->_db_data_check($data_id,$table,$field);
//        print_r(['$rs2'=>$rs]);
        if($rs){
            $data_exts['exts']    = json_encode($data_exts['exts']);
            unset($data_exts[$field]);

            $rs  = $this->_db_v->update($table,$data_exts," `{$field}` = ? ",$data_id);
            // $sql = $this->_db_v->sql();
            if($rs){
                echo " {$table} update ------ 更新成功 --> \${$field}:{$data_id} \n";
            }
//            else{
//                // echo "  error sql:{$sql}\n";
//                echo " {$table} update ------ 插入失败 --> \${$field}:{$data_id} \n";
//                return;
//            }
        }else{
            $data_exts['exts']    = json_encode($data_exts['exts']);
            $data_exts[$field] = $data_id;
            $this->_db_v->insert("`{$table}`",$data_exts);
            $sql = $this->_db_v->sql();

            // usleep(5000);
            $rs  = $this->_db_data_check($data_id,$table,$field);
//            print_r(['$rs'=>$rs]);
            if($rs){
                echo " {$table} ------ 数据 插入成功 --> \${$field}:{$data_id} \n";
            }else{
                echo "  error sql:{$sql}\n";
                echo "  error sql:{$this->_db_v->sql()}\n";
                echo " {$table} ------ 插入失败 --> \${$field}:{$data_id} \n";
            }
        }
    }

    /**
     * @param int $data_id
     * @return array|bool
     */
    protected function _db_data_check(int $data_id,string $table='data',string $field='data_id')
    {
        if($data_id)
        {
            $this->_db_v->active();
            $rs     =  $this->_db_v->row("SELECT * FROM `{$table}` where `{$field}` = ? limit 1;",$data_id);
            // echo  $this->_db_v->sql()."\n";
            if($rs && $rs[$field])
            {
                return $rs;
            }
        }
        return false;
    }

    /**
     * php ~/Transcend/www/com.ygcms.mm.2015/index.php zrun_api,url_refresh  mm.erldoc.com
     * php ~/Transcend/www/com.ygcms.mm.2015/index.php zrun_api,url_refresh www.383434.com
     *
     *
     * php /data/rbj_www.2015/index.php zrun_api,url_refresh  mm.erldoc.com
     * php /data/rbj_www.2015/index.php zrun_api,url_refresh www.383434.com
     *
     * 数据接口  输出接口
     *
     * @param $mod
     */
    public function url_refresh($mod)
    {
        $db   = self::db('core');
        $db->active();

        // 列表
        $bind =  [ 'loc' => '/','mod'=>'index', 'changefreq' => 'daily', 'lastmod' => time(), 'weight' => 1 , ];
        $db->insert(self::table_sitemap, $bind);
        // 分类
        foreach (\mm_pics::pics_class as $pic_class => $class_name){
            $bind       = [];
            $bind[]     = [ 'loc' => "/{$pic_class}/",'mod'=>'class_index', 'changefreq' => 'daily', 'lastmod' => time(), 'weight' => 0.9 , ];
            $rs         = $db->row("SELECT count(`pic_id`) as cc FROM `pic_data` where `pic_class` = ? ;",$pic_class);
            $count      = $rs['cc'];
            $total_page = ceil($count   / 18);
            echo "\$pic_class:{$pic_class} \$count:{$count} \$class_name:{$class_name} \$total_page:{$total_page}\n";
            if($total_page > 1)
            {
                $url0     = "/{$pic_class}/index_{page}.html";
                for ($page =1;$page < $total_page;$page++){
                    $url    = str_replace('{page}',$page,$url0);
                    $bind[] = [ 'loc' => $url,'mod'=>'class_list', 'changefreq' => 'weekly', 'lastmod' => time(), 'weight' => 0.8 , ];
                }
            }
            $db->insert(self::table_sitemap, $bind);
        }
        // 热门 最新
        $top_list = ['new2','gaoqing','hot','top'];
        $rs       = $db->row("SELECT count(`pic_id`) as cc FROM `pic_data` ;");
        $count    = $rs['cc'];
        foreach ($top_list as $pic_class){
            $bind       = [];
            $bind[]     = [ 'loc' => "/{$pic_class}/",'mod'=>'top_index', 'changefreq' => 'daily', 'lastmod' => time(), 'weight' => 0.8 , ];
            $total_page = ceil($count   / 16);
            echo "\$pic_class:{$pic_class} \$count:{$count} \$total_page:{$total_page}\n";
            if($total_page > 1)
            {
                $url0     = "/{$pic_class}/index_{page}.html";
                for ($page =1;$page < $total_page;$page++){
                    $url    = str_replace('{page}',$page,$url0);
                    $bind[] = [ 'loc' => $url,'mod'=>'top_list', 'changefreq' => 'weekly', 'lastmod' => time(), 'weight' => 0.7 , ];
                }
            }
            $db->insert(self::table_sitemap, $bind);
        }
        // tag   $tag  = $v['tag'];  $tag_count = $v['tag_count'];
        $tags  = $db->data_array("SELECT `tag_id`,`tag`,`tag_count` FROM  `pic_tag` where  `tag_count` >= 5  ORDER BY `tag_count` DESC;");
        foreach ($tags as $v){
            $bind       = [];
            $bind[]     = [ 'loc' => "/tag/".urlencode($v['tag']).".html",'mod'=>'tag_index', 'changefreq' => 'daily', 'lastmod' => time(), 'weight' => 0.8 , ];
            $rs         = $db->row("SELECT count(`pic_id`) as cc FROM `pic_tag_data` where `tag_id` = :tag_id  ;",$v);
            // echo $db->sql()."\n";
            $count      = $rs['cc'];
            $total_page = ceil($count   / 18);
            echo "\$pic_class:{$pic_class} \$count:{$count} \$tag:{$v['tag']} \$total_page:{$total_page}\n";
            if($total_page > 1)
            {
                $url0     = "/tag/".urlencode($v['tag'])."_{page}.html";
                for ($page =1;$page < $total_page;$page++){
                    $url    = str_replace('{page}',$page,$url0);
                    $bind[] = [ 'loc' => $url,'mod'=>'tag_list', 'changefreq' => 'weekly', 'lastmod' => time(), 'weight' => 0.7 , ];
                }
            }
            $db->insert(self::table_sitemap, $bind);
//            foreach ($bind as $v)
//            {
//                echo "{$v['loc']}\n";
//            }
        }
        // search  $tag = $v['tag'];    $tag_count = $v['tag_count'];
        foreach ($tags as $v){
            $bind       = [];
            $bind[]     = [ 'loc' => "/search/".urlencode($v['tag']).".html",'mod'=>'search_index', 'changefreq' => 'daily', 'lastmod' => time(), 'weight' => 0.8 , ];
            $rs         = $db->row("SELECT count(`pic_id`) as cc FROM `pic_data` WHERE  `pic_title` LIKE  ? or `pic_tag` LIKE  ?  ;", "%{$v['tag']}%");
            // echo $db->sql()."\n";
            $count      = $rs['cc'];
            $total_page = ceil($count   / 18);
            echo "\$pic_class:{$pic_class} \$count:{$count} \$tag:{$v['tag']} \$total_page:{$total_page}\n";
            if($total_page > 1)
            {
                $url0     = "/search/".urlencode($v['tag'])."_{page}.html";
                for ($page =1;$page < $total_page;$page++){
                    $url    = str_replace('{page}',$page,$url0);
                    $bind[] = [ 'loc' => $url,'mod'=>'search_list', 'changefreq' => 'weekly', 'lastmod' => time(), 'weight' => 0.7 , ];
                }
            }
            $db->insert(self::table_sitemap, $bind);
//            foreach ($bind as $v)
//            {
//                echo "{$v['loc']}\n";
//            }
        }
        // pic
        $pic_id    = 0;
        do{
            $rss =  $db->data_array("SELECT * FROM  `pic_data`  where `pic_id` > :pic_id order by `pic_id` asc limit 0,20;",['pic_id'=>$pic_id]);
            // echo $db->sql()."\n";
            if($rss)
            {
                $bind    = [];
                foreach ($rss as $rs)
                {
                    $pic_ext     = unserialize($rs['pic_ext']);
                    $page_total  = count($pic_ext);
                    for ($pic_page=1;$pic_page<=$page_total;$pic_page++)
                    {
                        if($pic_page > 1 )
                        {
                            $bind[]     = [ 'loc' => "/{$rs['pic_class']}/{$rs['pic_id']}_{$pic_page}.html",'mod'=>'pics_page', 'changefreq' => 'weekly', 'lastmod' => time(), 'weight' => 0.9 , ];
                        }else
                        {
                            $bind[]     = [ 'loc' => "/{$rs['pic_class']}/{$rs['pic_id']}.html",'mod'=>'pics_index', 'changefreq' => 'weekly', 'lastmod' => time(), 'weight' => 1 , ];
                        }
                    }
                    // echo "\$rs['pic_id']:{$rs['pic_id']}   \$page_total:".$page_total."\n";
                }
                $db->insert(self::table_sitemap, $bind);
                $pic_id = (int)$rs['pic_id'];
            }else
            {
                echo " <-- end\n\n";
                $pic_id = 0;
            }
        }while($pic_id);

    }



    /**
     * php ~/Transcend/www/com.ygcms.mm.2015/index.php zrun_api,data,pics_mm131,mm-erldoc-com  mm.erldoc.com
     * php ~/Transcend/www/com.ygcms.mm.2015/index.php zrun_api,data,pics_mm131,www-383434-com www.383434.com
     *
     * php ~/Transcend/www/com.ygcms.mm.2015/index.php zrun_api,data,pics_99mm,mm-erldoc-com  mm.erldoc.com
     * php ~/Transcend/www/com.ygcms.mm.2015/index.php zrun_api,data,pics_99mm,www-383434-com www.383434.com
     *
     *
     * php /data/rbj_www.2015/index.php zrun_api,data,pics_99mm,mm-erldoc-com  mm.erldoc.com
     * php /data/rbj_www.2015/index.php zrun_api,data,pics_99mm,www-383434-com www.383434.com
     *
     * php /data/rbj_www.2015/index.php zrun_api,data,pics_mm131,mm-erldoc-com  mm.erldoc.com
     * php /data/rbj_www.2015/index.php zrun_api,data,pics_mm131,www-383434-com www.383434.com
     *
     * 数据接口  输出接口
     *
     * @param $mod
     */

    public function data($mod)
    {
        $table     = trim($mod[1]);
        $domain0   = str_replace('-','.',trim($mod[2]));
        $domain    = str_replace('.','-',$domain0);
        $url       = "https://adm.383434.com/zrun_api/data/{$table}/{$domain}/";

        $funs      = [
            'pics_99mm'  => 'pic_data_99mm',
            'pics_mm131' => 'pic_data_mm131',
        ];
        $fun_db     = $funs[$table];
        if($fun_db)
        {
            $db        = self::db('core');
            $db->active();

            $c         = file_get_contents($url);
            $c2        = json_decode($c,true);
            if(1 == $c2['ret'])
            {
                $data = $c2['data'];
                if($data)
                {
                    foreach ($data as &$v)
                    {
                        $v['pic_centent'] = json_decode($v['pic_centent'],true);
                    }
                    $ids   = db::$fun_db($db,$data);
                    if($ids)
                    {
                        $url = "{$url}".implode('-',$ids)."/";
                        $c   =  file_get_contents($url);
                        db::pic_tag_count();                 // 刷新tag
                        $this->push_mip_map_today($mod); // 提交MIP
                        echo (date("Y-m-d H:i:s")." -> ".implode('-',$ids).':'.$c."\n");
                    }
                }
            }

            $this->url_refresh([]);
            exit("1:".$c2['error']."\n\n");
        }else
        {
            exit("-99:{$table} not db funs\n");
        }
    }

    /**
     * php ~/Transcend/www/com.ygcms.mm.2015/index.php zrun_api,push_mip_map_all  mm.erldoc.com
     * php ~/Transcend/www/com.ygcms.mm.2015/index.php zrun_api,push_mip_map_all www.383434.com
     *
     * php /data/rbj_www.2015/index.php zrun_api,push_mip_map_all  mm.erldoc.com
     * php /data/rbj_www.2015/index.php zrun_api,push_mip_map_all www.383434.com
     *
     * 数据接口  输出接口
     *
     * @param $mod
     */
    protected $_push_step = 1000;

    public function push_mip_map_all($mod)
    {
        // 提交Map
        $db        = self::db('core');
        $db->active();

        $this->_push_step = self::push_step;
        do{
            $do = $this->_push_all($db,self::type_baidu_map);
        }while($do);

        $this->_push_step = self::push_step;
        do{
            $do = $this->_push_all($db,self::type_baidu_mip);
        }while($do);
    }



    protected function _push_all(\ounun\mysqli $db,$type)
    {
        // $type = self::type_baidu_map;
        $max  = self::push_max[$type];
        $Ymd  = date('Ymd');
        $time = time();
        if($max){
            $cc   = $db->row("SELECT COUNT(`url_id`) as cc FROM `sitemap_push` WHERE `Ymd` = :Ymd and `target_id` = :target_id ;",['Ymd'=>$Ymd,'target_id'=>$type]);
            $cc   = (int)$cc['cc'];
            // echo $db->sql()."\n";
            echo "COUNT(`url_id`) as cc:{$cc}\n";
            if($cc < $max){
                $step_cc0  = $max - $cc;
                $step_cc   = $step_cc0 > $this->_push_step ? $this->_push_step : $step_cc0 ;
                $Ymd_start = date('Ymd',$time-self::push_rate);
                $rs        = $db->data_array("SELECT * FROM `sitemap` WHERE `url_id` NOT in ( SELECT `url_id` FROM `sitemap_push` WHERE `Ymd` >= :Ymd and `target_id` = :target_id ) ORDER BY `url_id` ASC LIMIT 0,".$step_cc.";",['Ymd'=>$Ymd_start,'target_id'=>$type]);
                if($rs){
                    $urls  = [];
                    $urls_m= [];
                    foreach ($rs as $v){
                        $urls[]   = $v['loc'];
                        if($type == self::type_baidu_map)
                        {
                            $urls_m[] = Const_Url_Www.substr($v['loc'],1);
                        }elseif ($type == self::type_baidu_mip)
                        {
                            $urls_m[] = Const_Url_Mip.substr($v['loc'],1);
                        }
                    }
//                    if($urls){
//                        $this->_push_db($urls,$type,$db);
//                    }
                    if($urls_m){
                        if($type == self::type_baidu_map)
                        {
                            $rs2         =  $this->push_map($urls_m);
//                            print_r($rs2);
                            $success     = (int)$rs2['success'];
                            $remain      = (int)$rs2['remain'];
//                            print_r([
//                                '$success' => $success,
//                                '$remain'  => $remain
//                            ]);
                            echo "\$urls_m-cc:".count($urls_m)."\n";
                            echo "\n\$success:{$success}  \$this->_push_step:{$this->_push_step}\n";
                            $this->_push_step  = $remain > self::push_step ? self::push_step : $remain;
                            if($success ==  count($urls_m))
                            {
                                $this->_push_db($urls,$type,$db);
                            }
                        }elseif ($type == self::type_baidu_mip)
                        {
                            $rs2         = $this->push_mip($urls_m);
//                            print_r($rs2);
                            $success_mip = (int)$rs2['success_mip'];
                            $remain_mip  = (int)$rs2['remain_mip'];
//                            print_r([
//                                '$success_mip' => $success_mip,
//                                '$remain_mip'  => $remain_mip
//                            ]);
                            echo "\$urls_m-cc:".count($urls_m)."\n";
                            echo "\$success_mip:{$success_mip}  \$this->_push_step:{$this->_push_step}\n";
                            $this->_push_step  = $remain_mip > self::push_step ? self::push_step : $remain_mip;
                            if($success_mip ==  count($urls_m))
                            {
                                $this->_push_db($urls,$type,$db);
                            }
                        }
                    }
                    return true;
                }
            }
        }
        return false;
    }
    /**
     * php ~/Transcend/www/com.ygcms.mm.2015/index.php zrun_api,push_mip_map_today  mm.erldoc.com
     * php ~/Transcend/www/com.ygcms.mm.2015/index.php zrun_api,push_mip_map_today www.383434.com
     *
     * php /data/rbj_www.2015/index.php zrun_api,push_mip_map_today  mm.erldoc.com
     * php /data/rbj_www.2015/index.php zrun_api,push_mip_map_today www.383434.com
     *
     * 数据接口  输出接口
     *
     * @param $mod
     */
    public function push_mip_map_today($mod)
    {
        $db          = self::db('core');
        $db->active();

        $bind        = [];
        $bind[]      = '';
        foreach (\mm_pics::pics_class as $pic_class => $class_name){
            $bind[]  = "{$pic_class}/";
        }
        $top_list    = ['new2','gaoqing','hot','top'];
        foreach ($top_list as $pic_class) {
            $bind[]  = "{$pic_class}/";
        }
        // $rss
        $db_table   = '`pic_data`';
        $time       = time() - 3600*8;
        $rss        = $db->data_array("SELECT * FROM {$db_table} where `add_time` > :add_time order by `pic_id` asc limit 0,1000;",['add_time'=>$time]);
        // echo $db->sql()."\n";
        if($rss)
        {
            foreach ($rss as $rs)
            {
                $pic_tag     = explode(',',$rs['pic_tag']);
                foreach ($pic_tag as $tag){
                    $bind[]  = "search/".urlencode($tag).".html";
                    $bind[]  = "tag/".urlencode($tag).".html";
                }

                $pic_ext     = unserialize($rs['pic_ext']);
                $page_total  = count($pic_ext);
                for ($pic_page=1;$pic_page<=$page_total;$pic_page++)
                {
                    if($pic_page > 1 )
                    {
                        $bind[]  = "{$rs['pic_class']}/{$rs['pic_id']}_{$pic_page}.html";
                    }else
                    {
                        $bind[]  = "{$rs['pic_class']}/{$rs['pic_id']}.html";
                    }
                }
            }
        }

        $urls_mip    = [];
        $urls_pc     = [];
        $urls_db     = [];
        foreach ($bind as  $url)
        {
            $mip        = Const_Url_Mip.$url;
            $pc         = Const_Url_Www.$url;

            $urls_mip[] = $mip;
            $urls_pc[]  = $pc;
            $urls_db[]  = "/{$url}";

            echo "mip:{$mip}  pc:{$pc}\n";
        }
        $db->update(self::table_sitemap,['lastmod'=>time()]," `loc` in (?) ",$urls_db);
        $this->_push_db($urls_db,self::type_baidu_mip,$db);
        $this->_push_db($urls_db,self::type_baidu_map,$db);
        // echo $db->sql()."\n\n";
        $rs =  $this->push_mip($urls_mip);
        echo "mip \$rs:{$rs}\n\n";
        $rs =  $this->push_map($urls_pc);
        echo "pc  \$rs:{$rs}\n\n";
    }

    protected function _push_db($urls, $type, \ounun\mysqli $db)
    {
        echo "_push_db-cc:".count($urls)."\n";
        $Ymd       = date('Ymd');
        $db->conn("INSERT INTO ".self::table_sitemap_push."(`url_id`, `Ymd`, `target_id`) SELECT `url_id`,{$Ymd},{$type} FROM ".self::table_sitemap." WHERE `loc` in ( ? );",$urls);
        // echo $db->sql()."\n";
    }

    public function push_mip($urls)
    {
        $api = Const_Mip_Api;
        echo ("\n\$urls:".count($urls)."\n\$api:{$api}\n");
        $ch  = curl_init();
        $options =  array(
            CURLOPT_URL => $api,
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS => implode("\n", $urls),
            CURLOPT_HTTPHEADER => array('Content-Type: text/plain'),
        );
        curl_setopt_array($ch, $options);
        $result = curl_exec($ch);
        echo date("Y-m-d H:i:s").' '.$result."\n\n";
        return json_decode($result,true);
    }

    public function push_map($urls)
    {
        $api = Const_Baidu_Api;
        echo ("\n\$urls:".count($urls)."\n\$api:{$api}\n");
        $ch  = curl_init();
        $options =  array(
            CURLOPT_URL => $api,
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS => implode("\n", $urls),
            CURLOPT_HTTPHEADER => array('Content-Type: text/plain'),
        );
        curl_setopt_array($ch, $options);
        $result = curl_exec($ch);
        echo date("Y-m-d H:i:s").' '.$result."\n\n";
        return json_decode($result,true);
    }
}
