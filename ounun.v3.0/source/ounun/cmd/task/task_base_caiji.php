<?php
namespace ounun\cmd\task;

use ounun\mvc\model\admin\purview;
use ounun\tool\db;
use ounun\tool\time;

abstract class task_base_caiji extends task_base
{
    /** @var int 默认(等待处理) */
    const Status_Data_Null = 0;
    /** @var int 正常 */
    const Status_Data_Ok = 1;
    /** @var int 出错(问题URL) */
    const Status_Data_Fail =   101;

    /** @var array 1:空置(等待) 2:运行中... 99:满载(过载) */
    const Status = [
        self::Status_Data_Null => '默认',
        self::Status_Data_Ok   => '正常',
        self::Status_Data_Fail => '出错',
    ];

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
    public static $caiji_tag = 'caiji_no1';
    /** @var string 采集  库标识(outs) 输出     （采集数据录入的数据库） */
    public static $caiji_libs_table_outs = '<tag>_outs';
    /** @var string 采集  库标识(data) 采集的数据（采集数据录入的数据库） */
    public static $caiji_libs_table_data   = '<tag>_<domain>_<data>';
    /** @var string 采集  库标识(数据02) - 表名 */
    public static $caiji_libs_table_data_2 = '';
    /** @var string 采集  库标识(数据03) - 表名 */
    public static $caiji_libs_table_data_3 = '';
    /** @var string 采集  库标识(tag)  */
    public static $caiji_libs_table_tag   = '';
    /** @var string 采集  库标识(封面) - 表名 */
    public static $caiji_libs_table_cover = '';
    /** @var string 采集  库标识(附件) - 表名 */
    public static $caiji_libs_table_attachment = '';

    /** @var string 采集  导出数据标识 */
    public static $caiji_out_table = 'outs_pics';
    /** @var string 根目录 */
    public static $caiji_res_dir_root   = '/data/ossfs_io3/';
    public static $caiji_res_dir_root_tag      = '{ossfs_io3}';
    /** @var string 根目录(加密文件) */
    public static $caiji_res_dir_root_m = '/data/ossfs_io3/';
    public static $caiji_res_dir_root_m_tag    = '{ossfs_io3}';
    /** @var string 采集  保存目录 */
    public static $caiji_res_dir_name   = '6mm';
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

    /** 数据01 - 《采集》任务 */
    public function data_01()
    {

    }

    /** 封面 - 《采集》任务 */
    public function cover()
    {

    }

    /** 附件 - 《采集》任务 */
    public function data_attachment()
    {

    }

    public function check_01()
    {
        manage::logs_msg_warning("数据check 没定义", __FILE__, __LINE__, time());
    }


    /**
     * 捡查指定字段是否都有数据
     * @param array $data
     * @param array $keys
     * @return bool
     */
    protected function _data_valid(array $data = [],array $keys = [])
    {
        $rs = true;
        foreach ($keys as $key){
            if($data && isset($data[$key]) && $data[$key]){
                $rs = true;
            }else{
                return false;
            }
        }
        return $rs;
    }


    /**
     * @param string $caiji_tag  类型标识
     * @param string $data_table 源表名
     * @param string $data_id    数据ID
     * @param string $remark     备注
     * @param array $extend      扩展数据
     */
    protected function _error_update(string $caiji_tag,string $data_table,string $data_id,string $remark= '',array $extend=[]){
        $bind = [
            'caiji_tag' => $caiji_tag,
            'data_table' => $data_table,
            'data_id' => $data_id,
            'status' => 1,
            'time_add' => \time(),
            'remark' => $remark,
            'extend' => json_encode_unescaped($extend)
        ];
        manage::db_biz()->table('`sys_caiji_error`')->insert($bind);
    }

    /**
     * 捡查指定字段数据是否存在
     * @param string $data_id
     * @param string $table_name
     * @param int $origin_level
     * @return bool
     */
    protected function _data_check(string $data_id, string $table_name, int $origin_level = 0)
    {
        $cc = manage::db_caiji()->table($table_name)->where(' `origin_level` =:origin_level  and  `data_id` =:data_id ',['origin_level'=>$origin_level,'data_id'=>$data_id])->count_value();
        if($cc){
            return true;
        }
        return false;
    }

    /**
     * @param string $data_id
     * @param string $table_name
     * @param int $origin_level
     * @return array
     */
    protected function _data_get(string $data_id, string $table_name, int $origin_level = 0)
    {
        $rs = manage::db_caiji()->table($table_name)->where(' `origin_level` =:origin_level  and  `data_id` =:data_id ',['origin_level'=>$origin_level,'data_id'=>$data_id])->column_one();
        if($rs && $rs['id']){
            return $rs;
        }
        return [];
    }

    /**
     * @param int $id
     * @param string $table_name
     * @return bool
     */
    protected function _data_check_id(int $id, string $table_name)
    {
        $cc = manage::db_caiji()->table($table_name)->where(' `id` =:id ',['id'=>$id])->count_value();
        if($cc){
            return true;
        }
        return false;
    }

    /**
     * @param int $id
     * @param string $table_name
     * @return array
     */
    protected function _data_get_id(int $id, string $table_name)
    {
        $rs = manage::db_caiji()->table($table_name)->where(' `id` =:id ',['id'=>$id])->column_one();
        if($rs && $rs['id']){
            return $rs;
        }
        return [];
    }

    /**
     * @param string $table_name
     * @return int  最后的data_id
     */
    protected function _data_last_id_get(string $table_name)
    {
        $rs = manage::db_caiji()->query("SELECT `data_id` FROM {$table_name} ORDER BY `id` DESC limit 0,1;")->column_one();
        if ($rs && $rs['data_id']) {
            return (int)$rs['data_id'];
        }
        return 0;
    }

    /**
     * @param array  $data          数据
     * @param string $data_id       数据id
     * @param int    $task_id       任务ID
     * @param string $origin_url    目标URL
     * @param string $origin_key    目标Key
     * @param bool   $is_update         true :更新   false:插入
     * @param bool   $is_update_default 数据插入 -> 本字段无效，
     *                                           数据更新 -> true:已默认字段数据为主  false:已字段数据为主
     * @return array
     */
    protected function _data_bind_caiji(array $data,string $data_id = "0",int $task_id = 0, string $origin_url = '', string $origin_key = '',
                                        bool $is_update = false, bool $is_update_default = false)
    {
        // print_r($data);
        $bind_default = [
         // 'id'            => ['default' => 0, 'type' => db::Type_Int], // 自增ID
            'data_id'       => ['default' => 0 , 'type' => db::Type_String], // 数据id
            'task_id'       => ['default' => 0,  'type' => db::Type_Int], // 任务ID
            'origin_url'    => ['default' => '', 'type' => db::Type_String], // 目标URL
            'origin_level'  => ['default' => 0 , 'type' => db::Type_Int],    // 级别

            'origin_key'       => ['default' => '',      'type' => db::Type_String], // 目标Key
            'origin_tag'       => ['default' => [],      'type' => db::Type_Json],   // 目标Tag(json)
            'origin_title'     => ['default' => '',      'type' => db::Type_String],    // 目标标题
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
            'extend'         => ['default' => [], 'type' => db::Type_Json],  // 任务参数paras/扩展json
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
        return db::bind($data,$bind_default,$is_update,$is_update_default);
    }

    /**
     * @param array $data
     * @param string $table_name
     * @param bool $is_update
     */
    protected function _data_insert(array $data, string $table_name,bool $is_update = false)
    {
        if($is_update){
            $id      = (int)$data['id'];
            if($id){
                $is = $this->_data_check_id($id, $table_name);
                if($is){
                    $data = $this->_data_bind_caiji($data,0,0,'','',true,false);
                    unset($data['id']);
                    manage::db_caiji()->table($table_name)->where(' `id` =:id ',['id'=>$id])->update($data);
                }else{
                    manage::logs_msg("warn->不已存在[{$table_name}]\$id:{$id}", manage::Logs_Warning);
                }
            }else{
                $data_id      = (string)$data['data_id'];
                $origin_level = (int)$data['origin_level'];
                if($data_id){
                    $is = $this->_data_check($data_id,$table_name,$origin_level);
                    if($is){
                        $data = $this->_data_bind_caiji($data,0,0,'','',true,false);
                        unset($data['id'],$data['data_id'],$data['origin_level']);
                        manage::db_caiji()->table($table_name)->where(' `origin_level` =:origin_level and `data_id` =:data_id ',['origin_level'=>$origin_level,'data_id'=>$data_id])->update($data);
                    }else{
                        manage::logs_msg("warn->不已存在[{$table_name}]\$data_id:{$data_id} \$origin_level:{$origin_level}", manage::Logs_Warning);
                    }
                }else{
                    manage::logs_msg("warn->数据有误[{$table_name}]\$data_id:{$data_id} \$origin_level:{$origin_level}", manage::Logs_Warning);
                }
            }
        }else{
            $id = (int)$data['id'];
            if($id){
                $is = $this->_data_check_id($id, $table_name);
                if($is){
                    manage::logs_msg("warn->已存在[{$table_name}]\$id:{$id}", manage::Logs_Warning);
                }else{
                    $data = $this->_data_bind_caiji($data,0,0,'','',false,false);
                    unset($data['id']);
                    manage::db_caiji()->table($table_name)->insert($data);
                }
            }else{
                $data_id      = (string)$data['data_id'];
                $origin_level = (int)$data['origin_level'];
                if($data_id){
                    $is = $this->_data_check($data_id,$table_name,$origin_level);
                    if($is){
                        manage::logs_msg("warn->已存在[{$table_name}]\$data_id:{$data_id} \$origin_level:{$origin_level}", manage::Logs_Warning);
                    }else{
                        $data = $this->_data_bind_caiji($data,0,0,'','',false,false);
                        unset($data['id']);
                        manage::db_caiji()->table($table_name)->insert($data);
                    }
                }else{
                    manage::logs_msg("warn->数据有误[{$table_name}]\$data_id:{$data_id} \$origin_level:{$origin_level}", manage::Logs_Warning);
                }
            }
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
