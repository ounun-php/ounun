<?php
namespace ounun\cmd\task\post_base;

use ounun\cmd\console;
use ounun\cmd\task\manage;
use ounun\cmd\task\task_base;
use ounun\mvc\model\admin\purview;

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
            manage::logs_msg("Successful post:{$this->_task_struct->task_id}/{$this->_task_struct->task_name}", $this->_logs_status,__FILE__,__LINE__,time());
        } catch (\Exception $e) {
            $this->_logs_status = manage::Logs_Fail;
            manage::logs_msg($e->getMessage(),$this->_logs_status,__FILE__,__LINE__,time());
            manage::logs_msg('Fail Coll tag:'.static::$tag.' tag_sub:'.static::$tag_sub, $this->_logs_status,__FILE__,__LINE__,time());
        }
    }


    /** 发布 01 */
    public function post_01()
    {
        print_r([ '$site_tag' => $this->struct_get()->site_tag, 'arguments' => $this->struct_get()->arguments,  ]);
    }
}
