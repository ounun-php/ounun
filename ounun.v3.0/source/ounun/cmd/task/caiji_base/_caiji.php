<?php

namespace ounun\cmd\task\caiji_base;

use ounun\api_sdk\com_showapi;
use ounun\cmd\console;
use ounun\cmd\task\manage;
use ounun\cmd\task\task_base;
use ounun\config;
use ounun\mvc\model\admin\purview;
use ounun\pdo;
use ounun\tool\time;

abstract class _caiji extends task_base
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
     * @return array
     */
    public function status()
    {
        $this->_logs_status = manage::Logs_Fail;
        manage::logs_msg("error:" . __METHOD__, $this->_logs_status,$this->_logs_status,__FILE__,__LINE__,time());
        return [];
    }

    /**
     * @param array $input
     * @param int $mode
     * @param bool $is_pass_check
     */
    public function execute(array $input = [], int $mode = manage::Mode_Dateup, bool $is_pass_check = false)
    {
        console::echo(__METHOD__, console::Color_Red);
        try {
            $this->_logs_status = manage::Logs_Succeed;

            manage::logs_msg("Successful update:{$this->_task_struct->task_id}/{$this->_task_struct->task_name}", manage::Logs_Succeed,$this->_logs_status,__FILE__,__LINE__,time());
        } catch (\Exception $e) {
            $this->_logs_status = manage::Logs_Fail;
            manage::logs_msg($e->getMessage(),$this->_logs_status,__FILE__,__LINE__,time());
            manage::logs_msg('Fail Coll tag:' . static::$tag . ' tag_sub:' . static::$tag_sub, manage::Logs_Fail,$this->_logs_status,__FILE__,__LINE__,time());
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

    /**
     * @param int $data_id
     * @param int $list_id
     * @param int $task_id
     * @param string $origin_url
     * @param int $origin_level
     * @param array $origin_data
     * @param int $is_status
     * @param int $is_wget_again
     * @param int $is_done
     * @param int $time_add
     * @param int $time_last
     * @param float $execution_time
     * @param array $extend
     * @return array
     */
    protected function _fields_data(int $data_id , int $list_id , int $task_id = 0, string $origin_url = '', int $origin_level = 0, array $origin_data = [], int $is_status = 0, int $is_wget_again = 0, int $is_done = 0, int $time_add = 0, int $time_last = 0, float $execution_time = 0, array $extend = [])
    {
        $bind = $this->_fields_list($list_id ,  $task_id , $origin_url, $origin_level ,  $origin_data ,  $is_status ,  $is_wget_again ,  $is_done ,  $time_add ,  $time_last ,  $execution_time , $extend);
        if ($data_id) {
            $bind['data_id'] = $data_id;
        }
        return $bind;
    }

    /**
     * @param int $list_id
     * @param int $task_id
     * @param string $origin_url
     * @param int $origin_level
     * @param array $origin_data
     * @param int $is_status
     * @param int $is_wget_again
     * @param int $is_done
     * @param int $time_add
     * @param int $time_last
     * @param float $execution_time
     * @param array $extend
     * @return array
     */
    protected function _fields_list(int $list_id , int $task_id = 0, string $origin_url = '', int $origin_level = 0, array $origin_data = [], int $is_status = 0, int $is_wget_again = 0, int $is_done = 0, int $time_add = 0, int $time_last = 0, float $execution_time = 0, array $extend = [])
    {
        if (empty($time_last)) {
            $time_last = \time();
        }
        if (empty($time_add)) {
            $time_add = \time();
        }
        $bind = [
            //  'list_id' => $list_id,
            'task_id' => $task_id,
            'origin_url' => $origin_url,
            'origin_level' => $origin_level,
            'origin_data' => json_encode_unescaped($origin_data),
            'is_status' => $is_status,
            'is_wget_again' => $is_wget_again,
            'is_done' => $is_done,
            'time_add' => $time_add,
            'time_last' => $time_last,
            'execution_time' => $execution_time,
            'extend' => json_encode_unescaped($extend),
        ];
        if ($list_id) {
            $bind['list_id'] = $list_id;
        }
        return $bind;
    }

    /**
     * @param array $data
     * @param string $table_name
     * @param string $fields_name
     */
    protected function _data_insert(array $data, string $table_name, string $fields_name = 'data_id')
    {
        $data_id = $data[$fields_name];
        $rs = $this->_data_check($data_id, $table_name, $fields_name);
        if (!$rs) {
            manage::db_caiji()->table($table_name)->insert($data);
            // echo $this->_db->sql()."\n";
            $is_insert = $this->_data_check($data_id, $fields_name);
            if ($is_insert) {
                manage::logs_msg("ok->[成功][{$table_name}]{$fields_name}:{$data_id}");
            } else {
                manage::logs_msg("error->[失败]数据插入[{$table_name}]{$fields_name}:{$data_id}", manage::Logs_Fail);
            }
        } else {
            manage::logs_msg("warn->已存在[{$table_name}]{$fields_name}:{$data_id}", manage::Logs_Warning);
        }
    }
}
