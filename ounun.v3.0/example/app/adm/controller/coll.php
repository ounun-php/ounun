<?php
/** 命名空间 */
namespace app\adm\controller;


class coll extends adm
{

    public function __construct($mod)
    {
        $pics    = $this->table_pics();
        if(!$_GET['table'] || !$pics[$_GET['table']])
        {
            $_GET['table'] = \ounun\page_util::val('table','pics_mm131');
        }
        \ounun\page_util::val_set('table',$_GET['table']);
        parent::__construct($mod);
    }

    /**
	 * 图片 - 资源库
	 * @param $mod array
	 */
	public function pics_list($mod)
	{
        // 权限
        $this->_nav_pur_check('coll/pics_list.html','coll@pics_list', '图片(采集)','采集',\adm_purv::nav_libs);

        $pics         = $this->table_pics();
        $table        = " `{$_GET['table']}` ";
        if ($_GET['act'] == 'del')
        {
            $this->_db_libs->delete($table,' `pic_id`= :pic_id ',$_GET);
            // 跳回原来的页面
            \ounun::go_back();
        }

        $where      = [];
        $where      = $where?' where '.implode(' and ', $where):'';
        /** 分页 */
        $page       = (int)$_GET['page'];
        $page       =      $page>1?$page:$page;
        $rows       = 20;
        $where_bind = $_GET;

        $url        = \ounun\page_util::url();
        $pg         = new \ounun\page($this->_db_libs,$table,$url,$where,$where_bind,'count(*)',\status::page_cfg,$rows);
        $ps         = $pg->init($page,"");

        $data	    = $this->_db_libs->data_array("select * from {$table} {$where} ORDER BY `pic_id` DESC limit {$pg->limit_start()},{$rows}", $where_bind);
        // echo $this->_db_libs->sql()."<br />\n";
        // print_r($this->_db_libs);

        require $this->require_file('_coll/pics_list.html.php');
	}
	
}
