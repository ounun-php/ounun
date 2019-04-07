<?php

namespace ounun\cmd\task\caiji_base;

use ounun\api_sdk\com_showapi;
use ounun\cmd\console;
use ounun\cmd\task\manage;
use ounun\cmd\task\task_base;
use ounun\config;
use ounun\mvc\model\admin\purview;
use ounun\pdo;

abstract class _caiji extends task_base
{
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
    public static $caiji_res_url_root= 'https://www.383434.com/';
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
        manage::logs_msg("error:" . __METHOD__, $this->_logs_status);
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

            manage::logs_msg("Successful update:{$this->_task_struct->task_id}/{$this->_task_struct->task_name}", manage::Logs_Succeed);
        } catch (\Exception $e) {
            $this->_logs_status = manage::Logs_Fail;
            manage::logs_msg($e->getMessage(), manage::Logs_Fail);
            manage::logs_msg("Fail Coll tag:{$this->_tag} tag_sub:{$this->_tag_sub}", manage::Logs_Fail);
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
     * @param int $data_id
     * @param string $fields_name
     * @return  bool
     */
    protected function data_check(int $data_id, string $fields_name = 'data_id')
    {
        return false;
    }


    /**
     * @param string $fields_name
     * @return int 最后的data_id
     */
    protected function data_last_id(string $fields_name = 'data_id')
    {
        return 0;
    }

    /**
     * 获取网络文件，并保存
     * @param string $url
     * @param string $file_save
     */
    protected function _wget_put(string $url, string $file_save)//,int $mini_size = 1024)
    {
        $do = static::$wget_loop_max;
        do {
            $do--;
            $c = \plugins\curl\http::file_get_contents($url, static::$caiji_src_url);
            if ($c && strlen($c) > static::$wget_file_mini_size) {
                $do = 0;
                file_put_contents($file_save, $c);
            }
        } while ($do);
    }

    /**
     * @param int $pic_id
     * @param array $data
     * @param array $pic_ext
     */
    protected function _wget_pics_base(int $pic_id, array $data, array $pic_ext)
    {
        $_is_save_db = false;
        //
        $dir_root = static::$caiji_src_url.static::$caiji_res_dir_name;
        $dir_pic = "{$dir_root}/{$pic_id}/";
        if (!file_exists($dir_pic)) {
            mkdir($dir_pic, 0777, true);
        }
        $cc = [];
        $ok_pic = [];

        // ------------------------------------------------------------------
        if ($data['cover']) {
            $url = $data['cover'];
            $file = "{$pic_id}/s.jpg";
            $file_full = "{$dir_root}/{$file}";
            if (!file_exists($file_full) || filesize($file_full) < $this->_wget_file_mini_size) {
                manage::logs_msg("id:{$pic_id} -> wget-s:{$url}");
                $this->_wget_put($url, $file_full);
            } else {
                $ok_pic[] = $file;
            }
            $cc[] = $file;
        }

        // ------------------------------------------------------------------
        foreach ($data['data'] as $v) {
            $url = $v['url'];
            $file = "{$pic_id}/{$v['file']}";
            $file_full = "{$dir_root}/{$file}";
            if (!file_exists($file_full) || filesize($file_full) < $this->_wget_file_mini_size) {
                manage::logs_msg("id:{$pic_id} -> wget-p:{$url}");
                $this->_wget_put($url, $file_full);
                $_is_save_db = true;
            } elseif ($url) {
                $ok_pic[] = $file;
                $_is_save_db = true;
            }
            $cc[] = $file;
        }
        if ($ok_pic) {
            manage::logs_msg("ok-pic id:{$pic_id}->:" . implode(',', $ok_pic));
        }
        $pic_centent = ['cover' => "{$pic_id}/s.jpg", 'data' => $cc];
        $bind = ['is_wget' => 1, 'pic_centent' => json_encode($pic_centent, JSON_UNESCAPED_UNICODE)];
        if ($_is_save_db && $pic_ext) {
            $bind['pic_ext'] = json_encode($pic_ext, JSON_UNESCAPED_UNICODE);
        }
        $this->_db_caiji->table($this->_table_list01)->where(' `pic_id` =:pic_id ', ['pic_id' => $pic_id])->update($bind);
        // echo $this->_db_libs->sql()."\n";
        // exit();
    }

    /**
     * @param int $pic_id
     * @param string $pic_title
     * @param array $pic_centent
     * @param array $pic_ext
     * @param string $pic_origin_url
     * @param array $tags
     * @param int $pic_goods
     * @param int $pic_collect_count
     * @param string $site_name
     * @param string $site_class_key
     * @param string $site_class_name
     * @param string $site_sub_key
     * @param string $site_sub_name
     * @param string $site_ext_key
     * @param string $site_ext_name
     * @param int $is_qiniu
     * @param int $is_wget
     * @param int $is_done
     * @param int $update_interval
     * @param int $update_time
     * @param int $update_count
     * @param int $add_time
     * @return array
     */
    protected function _fields_pics_v1(int $pic_id, string $pic_title, array $pic_centent, array $pic_ext, string $pic_origin_url,
                                       array $tags = [], int $pic_goods = 0, int $pic_collect_count = 1,
                                       string $site_name = '', string $site_class_key = '', string $site_class_name = '',
                                       string $site_sub_key = '', string $site_sub_name = '', string $site_ext_key = '', string $site_ext_name = '',
                                       int $is_qiniu = 0, int $is_wget = 1, int $is_done = 0,
                                       int $update_interval = 0, int $update_time = 0, int $update_count = 1, int $add_time = 0)
    {
        $time = time();
        $tags3 = com_showapi::tag($pic_title);
        if ($tags) {
            $tags3 = array_merge($tags, $tags3);
        }
        return [
            'pic_id' => $pic_id,
            'pic_goods' => $pic_goods,
            'pic_collect_count' => $pic_collect_count,
            'pic_title' => $pic_title,
            'pic_tag' => json_encode($tags3, JSON_UNESCAPED_UNICODE),
            'pic_centent' => json_encode($pic_centent, JSON_UNESCAPED_UNICODE), //\mm_pics::pics_class[$pic_class].", ".$data['pic_title'].", ".$pic_tag,
            'pic_ext' => json_encode($pic_ext, JSON_UNESCAPED_UNICODE),
            'pic_origin_url' => $pic_origin_url,

            'site_name' => $site_name,
            'site_class_key' => $site_class_key,
            'site_class_name' => $site_class_name,

            'site_sub_key' => $site_sub_key,
            'site_sub_name' => $site_sub_name,
            'site_ext_key' => $site_ext_key,
            'site_ext_name' => $site_ext_name,

            'is_qiniu' => $is_qiniu,
            'is_wget' => $is_wget,
            'is_done' => $is_done,

            'update_interval' => $update_interval,
            'update_time' => $update_time > 0 ? $update_time : $time,
            'update_count' => $update_count,
            'add_time' => $add_time > 0 ? $add_time : $time,
        ];
    }


    protected function _fields_v2(int $data_id, string $title, array $tags, array $data, array $data_origin, array $exts, string $origin_url,
                                  int $time_add = 0, int $coll_count = 0,
                                  array $coll_exts = [], array $site = [], array $is = [], array $update = [])
    {
        $tags3 = com_showapi::tag($title);
        if ($tags) {
            $tags3 = array_merge($tags, $tags3);
        }
        return [
            'data_id' => $data_id,
            'title' => $title,
            'tag' => json_encode($tags3, JSON_UNESCAPED_UNICODE),
            'data' => json_encode($data, JSON_UNESCAPED_UNICODE),
            'dataorigin' => json_encode($data_origin, JSON_UNESCAPED_UNICODE),
            'exts' => json_encode($exts, JSON_UNESCAPED_UNICODE),

            'origin_url' => $origin_url,
            'coll_count' => $coll_count,
            'coll_exts' => json_encode($coll_exts, JSON_UNESCAPED_UNICODE),

            'site_name' => $site['site_name'] ? $site['site_name'] : '',
            'site_class_key' => $site['site_class_key'] ? $site['site_class_key'] : '',
            'site_class_name' => $site['site_class_name'] ? $site['site_class_name'] : '',
            'site_sub_key' => $site['site_sub_key'] ? $site['site_sub_key'] : '',
            'site_sub_name' => $site['site_sub_name'] ? $site['site_sub_name'] : '',
            'site_ext_key' => $site['site_ext_key'] ? $site['site_ext_key'] : '',
            'site_ext_name' => $site['site_ext_name'] ? $site['site_ext_name'] : '',

            'is_data' => $is['is_data'] ? $is['is_data'] : 0,
            'is_wget' => $is['is_wget'] ? $is['is_wget'] : 0,
            'is_done' => $is['is_done'] ? $is['is_done'] : 0,
            'is_ext' => json_encode($is['is_ext'] ? $is['is_ext'] : [], JSON_UNESCAPED_UNICODE),

            'update_interval' => $update['update_interval'] ? $update['update_interval'] : 0,
            'update_time' => $update['update_time'] ? $update['update_time'] : 0,
            'update_count' => $update['update_count'] ? $update['update_count'] : 0,

            'time_add' => $time_add < 1 ? $time_add : time(),
        ];
    }

    protected function _fields_v2_files(int $data_id, string $dir, array $data = [])
    {
        $binds = [];
        foreach ($data as $v) {
            $binds[] = [
                'data_id' => $data_id,
                'type' => $v['type'],
                'dir' => $dir,
                'file' => $v['file'],
                'src_url' => $v['src_url'],
                'is_wget' => $v['is_wget'] ? $v['is_wget'] : 0,
                'is_done' => $v['is_done'] ? $v['is_done'] : 0,
                'time_add' => $v['time_add'] ? $v['time_add'] : time(),
                'exts' => ($v['exts'] && is_array($v['exts'])) ? json_encode($v['exts'], JSON_UNESCAPED_UNICODE) : '',
            ];
        }
        return $binds;
    }

    /**
     * @param array $data
     * @param string $fields_name
     */
    protected function data_insert(array $data, string $fields_name = 'data_id')
    {
        $data_id = $data[$fields_name];
        $rs = $this->data_check($data_id, $fields_name);
        if (!$rs) {
            $this->_db_caiji->table($this->_table_list01)->insert($data);
            // echo $this->_db->sql()."\n";
            $is_insert = $this->data_check($data_id, $fields_name);
            if ($is_insert) {
                manage::logs_msg("ok-> 成功 {$fields_name}:{$data_id}");
            } else {
                manage::logs_msg("sql:" . $this->_db_caiji->last_sql());
                manage::logs_msg("error-> 失败 {$fields_name}:{$data_id}", manage::Logs_Fail);
            }
        } else {
            manage::logs_msg("warn-> 已存在 {$fields_name}:{$data_id}", manage::Logs_Warning);
        }
    }
}
