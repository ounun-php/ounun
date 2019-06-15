<?php
namespace ounun\cmd\task;

use ounun\mvc\model\admin\purview;
use ounun\tool\db;

abstract class task_base_caiji extends task_base
{
    /** @var string 分类 */
    public static $tag = 'caiji';
    /** @var string 子分类 */
    public static $tag_sub = '';

    /** @var string 任务名称 */
    public static $name = '采集任务';
    /** @var string 定时 */
    public static $crontab = '{1-59} 3 * * *';
    /** @var int 最短间隔 */
    public static $interval = 86400;


    /** @var string 类型 */
    public static $site_type = purview::app_type_admin;
    /** @var string 采集  库标识（采集数据录入的数据库） */
    public static $caiji_libs = 'caiji_no1';
    /** @var string 采集  库标识(outs) 输出     （采集数据录入的数据库） */
    public static $caiji_libs_table_outs = '<tag>_outs';
    /** @var string 采集  库标识(data) 采集的数据（采集数据录入的数据库） */
    public static $caiji_libs_table_data = '<tag>_<domain>_<data>';

    /** @var string  列表01 - 表名 */
    public static $caiji_libs_table_list01 = 'libs_pics_list01';
    /** @var string  列表02 - 表名 */
    public static $caiji_libs_able_list02 = 'libs_pics_list02';
    /** @var string  列表03 - 表名 */
    public static $caiji_libs_table_list03 = 'libs_pics_list03';
    /** @var string  封面 - 表名 */
    public static $caiji_libs_table_cover = 'libs_pics_cover';
    /** @var string  数据01 - 表名 */
    public static $caiji_libs_table_data01 = 'libs_pics_data01';
    /** @var string  数据02 - 表名 */
    public static $caiji_libs_table_data02 = 'libs_pics_data02';
    /** @var string  数据03 - 表名 */
    public static $caiji_libs_table_data03 = 'libs_pics_data03';
    /** @var string  附件 - 表名 */
    public static $caiji_libs_table_attachment = 'libs_pics_attachment';

    /** @var string 采集  导出数据标识 */
    public static $caiji_out_table = 'outs_pics';
    /** @var string  根目录 */
    public static $caiji_res_dir_root = '/data/ossfs_io3/';
    /** @var string 采集  保存目录 */
    public static $caiji_res_dir_name = '6mm';
    /** @var string 采集  附件数据保存网站 */
    public static $caiji_res_url_root = 'https://www.383434.com/';
    /** @var string 采集  数据来源网站 */
    public static $caiji_src_site = '6mm.cc';
    /** @var string 采集  数据来源网站URL */
    public static $caiji_src_url = 'http://www.6mm.cc/';

    /** @var int 图片wget max */
    public static $wget_loop_max = 3;
    /** @var int 文件最小文件大小 */
    public static $wget_file_mini_size = 1024;

    /**
     * @param array $argc_input
     * @param int $argc_mode
     * @param bool $is_pass_check
     */
    public function execute(array $argc_input = [], int $argc_mode = manage::Mode_Dateup, bool $is_pass_check = false)
    {
        try {
            $this->_logs_status = manage::Logs_Succeed;

            manage::logs_msg("Successful update:{$this->_task_struct->task_id}/{$this->_task_struct->task_name}", $this->_logs_status,__FILE__,__LINE__,time());
        } catch (\Exception $e) {
            $this->_logs_status = manage::Logs_Fail;
            manage::logs_msg($e->getMessage(),$this->_logs_status,__FILE__,__LINE__,time());
            manage::logs_msg('Fail Coll tag:' . static::$tag . ' tag_sub:' . static::$tag_sub, $this->_logs_status,__FILE__,__LINE__,time());
        }
    }

    /** 列表01 - 《采集》任务 */
    public function list_01()
    {

    }

    /** 列表02 - 《采集》任务 */
    public function list_02()
    {

    }

    /** 列表03 - 《采集》任务 */
    public function list_03()
    {

    }

    /** 封面 - 《采集》任务 */
    public function cover()
    {

    }

    /** 数据01 - 《采集》任务 */
    public function data_01()
    {

    }

    /** 数据02 - 《采集》任务 */
    public function data_02()
    {

    }

    /** 数据03 - 《采集》任务 */
    public function data_03()
    {

    }

    /** 附件 - 《采集》任务 */
    public function data_attachment()
    {

    }

    /** 列表 - 《发布》任务 */
    public function post_site_data()
    {

    }

    /** 附件 - 《发布》任务 */
    public function post_site_attachment()
    {

    }

    /**
     * 捡查指定字段数据是否存在
     * @param int $data_id
     * @param string $table_name
     * @param string $fields_name
     * @return bool
     */
    protected function _data_check(int $data_id, string $table_name, string $fields_name = 'data_id')
    {
        return manage::db_caiji()->table($table_name)->is_repeat($fields_name, $data_id, \PDO::PARAM_INT);
    }

    /**
     * @param string $table_name
     * @param string $fields_name
     * @return int  最后的data_id
     */
    protected function _data_last_id_get(string $table_name, string $fields_name = 'data_id')
    {
        $rs = manage::db_caiji()->query("SELECT `{$fields_name}` FROM {$table_name} ORDER BY `{$fields_name}` DESC limit 0,1;")->column_one();
        if ($rs && $rs[$fields_name]) {
            return (int)$rs[$fields_name];
        }
        return 0;
    }

    /**
     * @param int $id             自增ID
     * @param int $data_id        数据id
     * @param string $origin_url  目标URL
     * @param string $origin_key  目标Key
     * @param string $origin_tag  目标Tag(json)
     * @param string $origin_title 目标标题
     * @param array $origin_data   目标数据(json)
     * @param array $origin_extend 扩展(json)
     * @param int $caiji_count     采集次数
     * @param int $is_wget_attachment 附件-是否采集
     * @param int $is_wget_data       内容-是否采集
     * @param int $is_done     是否完成
     * @param int $time_add    添加时间
     * @param int $time_update 更新时间
     * @return array
     */
    protected function _data_bind_caiji(array $data,int $id = 0,int $data_id = 0,int $task_id = 0, string $origin_url = '', string $origin_key = '',
                                        bool $is_update_force = false, bool $is_update_default = false)
    {
        // print_r($data);
        $bind_default = [
         // 'id'            => ['default' => 0, 'type' => db::Type_Int], // 自增ID
            'data_id'       => ['default' => 0 , 'type' => db::Type_Int], // 数据id
            'task_id'       => ['default' => 0,  'type' => db::Type_Int], //任务ID
            'origin_url'    => ['default' => '', 'type' => db::Type_String], // 目标URL
            'origin_level'  => ['default' => 0 , 'type' => db::Type_Int],    // 级别

            'origin_key'       => ['default' => '',      'type' => db::Type_String], // 目标Key
            'origin_tag'       => ['default' => [],      'type' => db::Type_Json],   // 目标Tag(json)
            'origin_title'     => ['default' => '',      'type' => db::Type_Int],    // 目标标题
            'origin_data'      => ['default' => [],      'type' => db::Type_Json],   // 目标数据(json)
            'origin_extend'    => ['default' => [],      'type' => db::Type_Json],   // 扩展(json)

            'caiji_count'         => ['default' => 0, 'type' => db::Type_Int], // 采集次数
            'is_status'           => ['default' => 0, 'type' => db::Type_Int], // 状态
            'is_wget_again'       => ['default' => 0, 'type' => db::Type_Int], // 重试-是否每天
            'is_wget_attachment'  => ['default' => 0, 'type' => db::Type_Int], // 附件-是否采集
            'is_wget_data'        => ['default' => 0, 'type' => db::Type_Int], // 内容-是否采集

            'is_done'      => ['default' => 0,     'type' => db::Type_Int], // 是否完成
            'is_del'       => ['default' => 0,     'type' => db::Type_Int], // 是否删除 1已删
            'time_add'     => ['default' => time(),'type' => db::Type_Int], // 添加时间
            'time_update'  => ['default' => 0,     'type' => db::Type_Int], // 更新时间
            'time_last'    => ['default' => 0,     'type' => db::Type_Int], // 完成时间

            'execution_time' => ['default' => 0, 'type' => db::Type_Float], // 执行时间(秒)
            'extend'         => ['extend' => [], 'type' => db::Type_Json],  // 任务参数paras/扩展json
        ];
        if($data_id){
            $data['data_id'] = $data_id;
        }
        if($task_id){
            $data['task_id'] = $task_id;
        }
        if($origin_url){
            $data['origin_url'] = $origin_url;
        }
        if($origin_key){
            $data['origin_key'] = $origin_key;
        }
        return db::bind($data,$bind_default,$is_update_force,$is_update_default);
    }

    /**
     * @param array $data
     * @param string $table_name
     * @param string $fields_name
     */
    protected function _data_insert(array $data, string $table_name, string $fields_name = 'data_id',bool $is_replace = false)
    {
        $data_id = $data[$fields_name];
        $rs = $this->_data_check($data_id, $table_name, $fields_name);
        if (!$rs) {
            manage::db_caiji()->table($table_name)->insert($data);
            // echo $this->_db->sql()."\n";
            $is_insert = $this->_data_check($data_id, $table_name, $fields_name);
            if ($is_insert) {
                manage::logs_msg("ok->[成功][{$table_name}]{$fields_name}:{$data_id}");
            } else {
                manage::logs_msg("error->[失败]数据插入[{$table_name}]{$fields_name}:{$data_id}", manage::Logs_Fail);
            }
        } else {
            manage::logs_msg("warn->已存在[{$table_name}]{$fields_name}:{$data_id}", manage::Logs_Warning);
        }
    }

    /**
     * 获取网络文件，并保存
     * @param string $url
     * @param string $save_filename
     * @param string $referer
     */
    protected function _wget_attachment_save(string $url, string $save_filename, string $referer = '')
    {
        if (empty($referer)) {
            $referer = static::$caiji_src_url;
            if (empty($referer)) {
                $referer = $url;
            }
        }
        $do = static::$wget_loop_max;
        do {
            $do--;
            $c = \plugins\curl\http::file_get_contents($url, $referer);
            if ($c && strlen($c) > static::$wget_file_mini_size) {
                $do = 0;
                file_put_contents($save_filename, $c);
            }
        } while ($do);
    }
}
