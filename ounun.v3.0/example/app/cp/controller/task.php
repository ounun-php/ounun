<?php
namespace app\cp\controller;


use app\adm\model\purview;
/**
 * 任务
 * @author dreamxyp
 */
class task extends adm
{

    /**
     * 定时任务
     * @param array $mod
     */
    public function index($mod)
    {
        // $this->_site_type_only = [purview::app_type_admin];
        $this->_nav_pur_check('task/','task@index', '定时任务','任务',purview::nav_site);

        // $db_www      = self::db('libs');
        $table          = '`z_task`';

        $this->_db_site->active();
        if ($_POST)
        {
            $this->_task_post($_POST,$table);
            exit();
        }elseif($_GET && 'add' == $_GET['act'])
        {
            $rs     = [];
            if($_GET['task_id'])
            {
                $rs =  $this->_db_site->row("SELECT * FROM {$table}  where `task_id` = :task_id limit 0,1;",$_GET);
            }
            require \v::tpl_fixed('_task/task_add.html.php');
            exit();
        }elseif($_GET && $_GET['task_id'] && 'del' == $_GET['act'])
        {
            $this->_db_site->delete($table,'  `task_id` = :task_id  ',$_GET);

            go_url(\ounun\page_util::page('/task/'));
        }

        /** 分页 */
        $where      = [];
        $where      = $where?' where '.implode(' and ', $where):'';
        $page       = (int)$_GET['page'];
        $page       =      $page>1?$page:$page;
        $rows       = 20;
        $where_bind = $_GET;
        //
        $url        = \ounun\page_util::url();
        $pg         = new \ounun\page( $this->_db_site,$table,$url,$where,$where_bind,'count(*)',\status::page_cfg,$rows);
        $ps         = $pg->init($page,"");

        $site_info  = ['site_tag'=>self::$auth->cookie_get(purview::cp_site),'zqun_tag'=>self::$auth->cookie_get(purview::cp_zqun)];
        $zqun_cc    = $this->_db_v->fetch_assoc('SELECT `zqun_tag`, `dir` FROM `adm_zqun` where `zqun_tag` = :zqun_tag ;',$site_info,'zqun_tag');
        // echo $this->_db_v->sql()."<br />\n";

        $cmd_serv   = "{$zqun_cc[$site_info['zqun_tag']]['dir']}/app.{$site_info['site_tag']}/index.php";
        $cmd_local  = str_replace("/www/wwwroot/cms.","~/Transcend/www.cms.",$cmd_serv);
        $datas      = $this->_db_site->data_array("select * from {$table} {$where} ORDER BY `task_id` DESC limit {$pg->limit_start()},{$rows}", $where_bind);
        require \v::tpl_fixed('_task/task.html.php');
    }

    protected function _task_post(array $post,string $table)
    {
//        print_r($post);
//        exit();
        $post['task_id']   =  (int)$post['task_id'];
        $bind         = [
            'task_name'    => $post['task_name'],
            'type'         => $post['type'],
            'crontab'      => $post['crontab'],
            'interval'     => $post['type']?$post['interval1']:$post['interval0'],
            'args'         => json_encode($post['args'],JSON_UNESCAPED_UNICODE),
         // 'ignore'       => $post['ignore'],
         // 'time_add'     => $post['time_add'],
            'time_begin'   => strtotime($post['time_begin']." 00:00:00"),
            'time_end'     => strtotime($post['time_end']." 23:59:59"),
         // 'time_last'    => $post['time_last'],
         // 'times'        => $post['times'],
        ];
        if($post['task_id'])
        {
            $this->_db_site->update($table,$bind," `task_id` = :task_id ",$post);
        }else
        {
            $bind['time_add'] = time();
            $this->_db_site->insert($table,$bind);
        }
//        echo $db_www->sql().'<br />';
//        exit();
        go_url(\ounun\page_util::page('/task/'));
    }

    /**
     * 任务日志
     * @param array $mod
     */
    public function logs($mod)
    {
        // 权限
        // $this->_site_type_only = [purview::app_type_admin];
        $this->_nav_pur_check('task/logs.html','task@logs', '任务日志','任务',purview::nav_site);

        // $db_www = self::db('libs');
        $table  = '`z_task_logs`';
        if($_GET && $_GET['id'] && 'del' == $_GET['act'])
        {
            $this->_db_site->delete($table,' `id` = :id  ',$_GET);
            go_url($this->_page_url);
        }

        /** 分页 */
        $where      = [];
        $where      = $where?' where '.implode(' and ', $where):'';
        $page       = (int)$_GET['page'];
        $page       =      $page>1?$page:$page;
        $rows       = 20;
        $where_bind = $_GET;
        //
        $url        = \ounun\page_util::url();
        $pg         = new \ounun\page( $this->_db_site,$table,$url,$where,$where_bind,'count(*)',\status::page_cfg,$rows);
        $ps         = $pg->init($page,"");

        $datas      = $this->_db_site->data_array("select * from {$table} {$where} ORDER BY `id` DESC limit {$pg->limit_start()},{$rows}", $where_bind);
        $task_ids   = [];
        foreach ($datas as $v)
        {
            $task_ids[$v['task_id']] = $v['task_id'];
        }
        $task_ids   = array_values($task_ids);
        $tasks0     = $this->_db_site->data_array("SELECT `task_id`,`task_name` FROM `z_task` where `task_id` in (?) ;",$task_ids);
        $tasks      = [];
        foreach ($tasks0 as $v)
        {
            $tasks[$v['task_id']] = $v['task_name'];
        }
        require \v::tpl_fixed('_task/task_logs.html.php');
    }
}