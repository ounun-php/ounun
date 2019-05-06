<?php

namespace ounun\cmd\task\post_base;

use ounun\cmd\task\manage;
use ounun\cmd\task\task_base;
use ounun\mvc\c;
use ounun\mvc\model\admin\purview;
use ounun\pdo;

abstract class _post extends task_base
{
    /** @var string 分类 */
    public static $tag = 'post';
    /** @var string 子分类 */
    public static $tag_sub = '';

    /** @var string 任务名称 */
    public static $name = '更新内容 [post]';
    /** @var string 定时 */
    public static $crontab = '{1-59} 8 * * *';
    /** @var int 最短间隔 */
    public static $interval = 86400;
    /** @var string 类型 */
    public static $site_type = purview::app_type_site;


    /** @var string  网站数据 - 数据 - 表名 */
    public static $table_site_data = '';
    /** @var string  网站数据 - 附件 - 表名 */
    public static $table_site_attachment = '';

    /** @var string  outs数据 - 网站 - 表名 */
    public static $table_caiji_post_outs = '';


    /**
     * @param array $argc_input
     * @param int $argc_mode
     * @param bool $is_pass_check
     */
    public function execute(array $argc_input = [], int $argc_mode = manage::Mode_Dateup, bool $is_pass_check = false)
    {
        // $site_tag = ($argc_input && is_array($argc_input)) ? ((int)array_shift($argc_input)) : '';
        try {
            $this->post_01();
            $this->_logs_status = manage::Logs_Succeed;
            manage::logs_msg("Successful post:{$this->_task_struct->task_id}/{$this->_task_struct->task_name}", $this->_logs_status, __FILE__, __LINE__, time());
        } catch (\Exception $e) {
            $this->_logs_status = manage::Logs_Fail;
            manage::logs_msg($e->getMessage(), $this->_logs_status, __FILE__, __LINE__, time());
            manage::logs_msg('Fail Coll tag:' . static::$tag . ' tag_sub:' . static::$tag_sub, $this->_logs_status, __FILE__, __LINE__, time());
        }
    }


    /** 发布 01 */
    public function post_01()
    {
        print_r(['$site_tag' => $this->struct_get()->site_tag, 'arguments' => $this->struct_get()->arguments,]);
    }

    /**
     * @param pdo $db_caiji
     * @param string $site_tag
     * @param string $source_table
     * @param string $source_table_fields
     * @param string $source_table_fields_data_id
     * @param int $Ymd
     * @param int $init_Ymd
     * @param int $init_count
     * @param int $count_min
     * @param int $count_max
     * @return array
     */
    public function export(pdo $db_caiji, string $site_tag, string $source_table,
                           int $count_min = 2, int $count_max = 6,
                           string $source_table_fields = ' * ',
                           string $source_table_fields_data_id = ' `data_id` ',
                           int $Ymd = 0, int $init_Ymd = 0, int $init_count = 0)
    {
        $source_table_data = str_replace('`','',$source_table);
        // outs数据表
        $table_caiji_post_outs = static::$table_caiji_post_outs;
        if (empty(static::$table_caiji_post_outs)) {
            return error("提示:static::\$stable_caiji_post_outs有误错误！");
        }

        if (empty(static::$table_caiji_post_outs)) {
            return error("提示:static::\$stable_caiji_post_outs有误错误！");
        }

        // 捡查数据
        $Ymd = $Ymd ? $Ymd : date('Ymd');
        $bind = [
            'site_tag' => $site_tag,
            'source_table' => $source_table_data,
            'Ymd' => $Ymd,
            'status' => c::Status_No
        ];

        // 获得现在ids
        $ids = [];
        if ($init_Ymd && $init_count && $init_Ymd == $Ymd) {
            $rs = $db_caiji->query("SELECT COUNT( `data_id`) as `cc`,`status` FROM {$table_caiji_post_outs} WHERE `site_tag` =:site_tag and `source_table` =:source_table  GROUP by `status`;", $bind)
                ->assoc('status')->column_all();
            $status_1 = (int)$rs[1]['cc'];
            $status_0 = (int)$rs[0]['cc'];
            $status_cc = $status_1 + $status_0;
            //
            if ($status_cc < $init_count) {
                if ($status_0 > 0) {
                    $rs = $db_caiji->query("SELECT `data_id`,`status` FROM {$table_caiji_post_outs} WHERE `site_tag` =:site_tag and `source_table` =:source_table and `status` = :status ;", $bind)->column_all();
                    if ($rs && is_array($rs)) {
                        foreach ($rs as $v) {
                            if ($v && $v['data_id'] && c::Status_No == $v['status']) {
                                $ids[] = $v['data_id'];
                            }
                        }
                    }
                }
                if ($ids && is_array($ids)) {
                    $is_loop = true;
                    $is_rand = false;
                } else {
                    $is_loop = true;
                    $is_rand = true;
                }
            } else {
                $is_loop = false;
                $is_rand = false;
            }
        } else {
            $is_loop = false;
            $is_rand = true;
            $rs = $db_caiji->query("SELECT `data_id`,`status` FROM {$table_caiji_post_outs} WHERE `site_tag` =:site_tag and `source_table` =:source_table and `Ymd` =:Ymd ;", $bind)->column_all();
            if ($rs && is_array($rs)) {
                foreach ($rs as $v) {
                    if ($v && $v['data_id'] && c::Status_No == $v['status']) {
                        $ids[] = $v['data_id'];
                    }
                }
            }
        }

        // 获得数据
        if ($ids && is_array($ids)) {
            $rs = $db_caiji->query("SELECT {$source_table_fields} FROM {$source_table} WHERE {$source_table_fields_data_id} in (?);", $ids)->column_all();
            return succeed($rs, '', ['ids' => $ids, 'loop' => $is_loop]);
        } elseif ($is_rand) {
            // 读取数据
            $bind = [
                'site_tag' => $site_tag,
                'source_table' => $source_table_data
            ];
            $cc = rand($count_min, $count_max);
            $rs = $db_caiji->query("SELECT {$source_table_fields} FROM {$source_table} WHERE `is_del` = 0 and {$source_table_fields_data_id} not IN (SELECT `data_id` FROM {$table_caiji_post_outs} WHERE `site_tag` = :site_tag and `source_table` = :source_table ) ORDER BY RAND() LIMIT {$cc};", $bind)->column_all();
            if ($rs) {
                $ids = [];
                $bind = [];
                foreach ($rs as $v) {
                    $ids[] = $v['data_id'];
                    $bind[] = [
                        'Ymd' => $Ymd,
                        'status' => c::Status_No,
                        'site_tag' => $site_tag,
                        'source_table' => $source_table_data,
                        'data_id' => $v['data_id']
                    ];
                }
                $db_caiji->table($table_caiji_post_outs)->multiple(true)->insert($bind); // 获得
                return succeed($rs, '', ['ids' => $ids, 'loop' => $is_loop]);
            } else {
                return succeed([], '没数据了', ['ids' => $ids, 'loop' => $is_loop]);
            }
        }
        return succeed([], '', ['ids' => $ids, 'loop' => $is_loop]);
    }

    /**
     * 获得采集源 数据
     * @param pdo $db_caiji
     * @param string $source_table
     * @return int
     */
    public function export_count_value(pdo $db_caiji,string $source_table)
    {
        return $db_caiji->table($source_table)->where(' `is_del` = 0 ')->count_value();
    }

    /**
     * 数据导出  -- 更新
     * @param pdo $db_caiji
     * @param string $site_tag
     * @param string $source_table
     * @param array $ids
     * @return array
     */
    public function export_dateup(pdo $db_caiji, string $site_tag, string $source_table, array $ids = [])
    {
        $source_table_data = str_replace('`','',$source_table);
        // outs数据表
        if (empty(static::$table_caiji_post_outs)) {
            return error("提示:static::\$stable_caiji_post_outs有误错误！");
        } elseif (empty($site_tag)) {
            return error('提示:$site_tag为空...');
        } elseif (empty($source_table)) {
            return error('提示:$source_table为空...');
        } elseif (empty($ids)) {
            return error('提示:$ids为空...');
        }

        $bind = [
            'site_tag' => $site_tag,
            'source_table' => $source_table_data,
           // 'data_id' => implode(',',$ids),
        ];
        $where_bind = [];
        foreach ($ids as $data_id){
            $where_bind[] = [ 'data_id' => $data_id ];
        }
        $rs = $db_caiji->table(static::$table_caiji_post_outs)
            ->where("`site_tag` =:site_tag and `source_table` =:source_table   ", $bind)
            ->update(['status' => c::Status_Yes],[],' and `data_id` = :data_id ',$where_bind);

        if ($rs) {
            return succeed($rs);
        }
        $db_caiji->stmt()->debugDumpParams();
        // print_r(['$db_caiji->stmt()->queryString'=>$db_caiji->stmt()->queryString,'$bind'=>$bind]);
        return error('更新失败');
    }
}
